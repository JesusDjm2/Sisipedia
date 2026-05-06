<?php

namespace App\Console\Commands;

use App\Models\sisipedia\Category;
use App\Models\sisipedia\CategoryFile;
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateLegacySisipedia extends Command
{
    protected $signature = 'sisipedia:migrar-antiguo
        {--old-db=sisiold : Base de datos del sistema antiguo}
        {--old-root=c:\xampp\htdocs\SisipediaAntiguo : Ruta raíz del sistema antiguo}
        {--with-pdfs : Sube y vincula PDFs del campo Informe}
        {--dry-run : Simula la migración sin escribir en BD}
        {--limit=0 : Limita la cantidad de indicadores a procesar (0 = todos)}';

    protected $description = 'Migra jerarquía y PDFs desde SisipediaAntiguo a categories/category_files.';

    private array $legacyToNew = [];
    private int $created = 0;
    private int $reused = 0;
    private int $pdfLinked = 0;
    private int $pdfSkipped = 0;
    private int $pdfMissing = 0;
    private array $pdfDriveIdByPath = [];

    public function handle(): int
    {
        $oldDb = (string) $this->option('old-db');
        $oldRoot = rtrim((string) $this->option('old-root'), '\\/');
        $withPdfs = (bool) $this->option('with-pdfs');
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if (! is_dir($oldRoot)) {
            $this->error("No existe la ruta old-root: {$oldRoot}");
            return self::FAILURE;
        }

        $this->info('Iniciando migración SisipediaAntiguo...');
        $this->line("BD origen: {$oldDb}");
        $this->line("Ruta origen: {$oldRoot}");
        $this->line('Modo: '.($dryRun ? 'DRY-RUN (sin cambios)' : 'REAL'));
        $this->line('Migrar PDFs: '.($withPdfs ? 'Sí' : 'No'));
        $this->newLine();

        $legacy = DB::table("{$oldDb}.tindicadores")
            ->select(['IdIndicador', 'Indicador', 'IdPertenencia', 'Detalle', 'Informe', 'Pagina', 'Tipo'])
            ->orderBy('IdIndicador');

        if ($limit > 0) {
            $legacy->limit($limit);
        }

        $rows = $legacy->get();
        if ($rows->isEmpty()) {
            $this->warn('No hay registros en tindicadores.');
            return self::SUCCESS;
        }

        // 1) Crear/reusar categorías preservando jerarquía.
        $pending = $rows->keyBy('IdIndicador');
        $lastPending = -1;

        while ($pending->isNotEmpty() && $pending->count() !== $lastPending) {
            $lastPending = $pending->count();

            foreach ($pending as $legacyId => $row) {
                $parentLegacyId = $row->IdPertenencia ? (int) $row->IdPertenencia : null;
                $parentNewId = null;

                if ($parentLegacyId !== null) {
                    $parentNewId = $this->legacyToNew[$parentLegacyId] ?? null;
                    if ($parentNewId === null) {
                        continue; // Espera a que su padre exista.
                    }
                }

                $name = $this->cleanText((string) $row->Indicador);
                if ($name === '') {
                    $name = 'Registro '.$legacyId;
                }

                $existing = Category::query()
                    ->where('parent_id', $parentNewId)
                    ->where('name', $name)
                    ->first();

                if ($existing) {
                    $category = $existing;
                    $this->reused++;
                } else {
                    $categoryData = [
                        'name' => $name,
                        'description' => $name, // requisito del usuario
                        'slug' => $this->uniqueSlug($name, $legacyId),
                        'parent_id' => $parentNewId,
                        'order' => $this->nextOrder($parentNewId),
                        'is_active' => true,
                    ];

                    if ($dryRun) {
                        $category = new Category($categoryData);
                        // ID temporal negativo para mantener relación en simulación.
                        $category->id = -1 * (int) $legacyId;
                    } else {
                        $category = Category::create($categoryData);
                    }
                    $this->created++;
                }

                $this->legacyToNew[(int) $legacyId] = (int) $category->id;
                $pending->forget($legacyId);
            }
        }

        if ($pending->isNotEmpty()) {
            $this->warn('Algunos registros no pudieron enlazar padre/hijo (posibles padres faltantes): '.$pending->count());
        }

        // 2) Migrar PDF desde Informe si se solicita.
        if ($withPdfs) {
            $drive = $dryRun ? null : app(GoogleDriveService::class);
            foreach ($rows as $row) {
                $legacyId = (int) $row->IdIndicador;
                $categoryId = $this->legacyToNew[$legacyId] ?? null;
                if (! $categoryId || $categoryId < 1) {
                    continue;
                }

                $informe = $this->cleanText((string) ($row->Informe ?? ''));
                if ($informe === '' || ! Str::endsWith(Str::lower($informe), '.pdf')) {
                    $this->pdfSkipped++;
                    continue;
                }

                $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $informe);
                $fullPath = $oldRoot.DIRECTORY_SEPARATOR.$relativePath;
                if (! file_exists($fullPath)) {
                    $this->pdfMissing++;
                    continue;
                }

                $displayName = basename($fullPath);
                $alreadyLinked = CategoryFile::query()
                    ->where('category_id', $categoryId)
                    ->where('tipo', 'pdf')
                    ->where('nombre_original', $displayName)
                    ->exists();

                if ($alreadyLinked) {
                    $this->pdfSkipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->pdfLinked++;
                    continue;
                }

                try {
                    $driveId = $this->pdfDriveIdByPath[$fullPath] ?? null;
                    if (! $driveId) {
                        $uploaded = new UploadedFile(
                            $fullPath,
                            $displayName,
                            'application/pdf',
                            null,
                            true
                        );
                        $driveId = $this->uploadPdfToDriveWithRetries($drive, $fullPath, $displayName);
                        $this->pdfDriveIdByPath[$fullPath] = $driveId;
                    }

                    CategoryFile::create([
                        'category_id' => $categoryId,
                        'tipo' => 'pdf',
                        'drive_id' => $driveId,
                        'nombre_original' => $displayName,
                        'orden' => $this->nextFileOrder($categoryId),
                    ]);

                    $this->pdfLinked++;
                } catch (\Throwable $e) {
                    $this->pdfSkipped++;
                    $this->warn("PDF no migrado para legado {$legacyId}: ".$e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info('Migración finalizada.');
        $this->line("Categorías creadas: {$this->created}");
        $this->line("Categorías reutilizadas: {$this->reused}");
        if ($withPdfs) {
            $this->line("PDF vinculados: {$this->pdfLinked}");
            $this->line("PDF omitidos: {$this->pdfSkipped}");
            $this->line("PDF no encontrados en disco: {$this->pdfMissing}");
        }

        return self::SUCCESS;
    }

    /**
     * Reintenta subidas a Drive ante códigos transitorios (503/500/429).
     */
    private function uploadPdfToDriveWithRetries(GoogleDriveService $drive, string $fullPath, string $displayName): string
    {
        $attempts = 5;
        $last = null;

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $uploaded = new UploadedFile(
                    $fullPath,
                    $displayName,
                    'application/pdf',
                    null,
                    true
                );

                return $drive->upload($uploaded, 'pdf', $displayName);
            } catch (\Throwable $e) {
                $last = $e;
                if ($i < $attempts && $this->isRetryableDriveError($e)) {
                    $delayMs = random_int(2000, 6000);
                    usleep($delayMs * 1000);
                    continue;
                }
                throw $e;
            }
        }

        throw $last ?? new \RuntimeException('Fallo indefinido al subir PDF a Drive.');
    }

    private function isRetryableDriveError(\Throwable $e): bool
    {
        $code = $e->getCode();
        if (in_array($code, [429, 500, 503], true)) {
            return true;
        }

        $msg = $e->getMessage();

        return str_contains($msg, '503')
            || str_contains($msg, '429')
            || str_contains($msg, 'Transient')
            || str_contains($msg, 'transient')
            || str_contains($msg, 'Internal Error')
            || str_contains($msg, 'rateLimitExceeded')
            || str_contains($msg, 'userRateLimitExceeded');
    }

    private function cleanText(string $value): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $decoded) ?? '');
    }

    private function nextOrder(?int $parentId): int
    {
        return ((int) Category::query()->where('parent_id', $parentId)->max('order')) + 1;
    }

    private function nextFileOrder(int $categoryId): int
    {
        return ((int) CategoryFile::query()->where('category_id', $categoryId)->max('orden')) + 1;
    }

    private function uniqueSlug(string $name, int $legacyId): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'registro-'.$legacyId;
        }

        $slug = $base;
        $i = 1;
        while (Category::query()->where('slug', $slug)->exists()) {
            $i++;
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}

