<?php

namespace App\Console\Commands;

use App\Models\sisipedia\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListLegacyMissingPdfs extends Command
{
    protected $signature = 'sisipedia:listar-pdfs-faltantes
        {--old-db=sisiold : Base de datos del sistema antiguo}
        {--old-root=c:\\xampp\\htdocs\\SisipediaAntiguo : Carpeta raíz SisipediaAntiguo}
        {--csv : Salida como CSV por filas para copiar}';

    protected $description = 'Lista registros legacy cuyo PDF (Informe) no existe en disco, con id de categoría nueva.';

    private array $legacyToNew = [];

    public function handle(): int
    {
        $oldDb = (string) $this->option('old-db');
        $oldRoot = rtrim((string) $this->option('old-root'), '\\/');
        $csv = (bool) $this->option('csv');

        if (! is_dir($oldRoot)) {
            $this->error("No existe old-root: {$oldRoot}");

            return self::FAILURE;
        }

        $rows = DB::table("{$oldDb}.tindicadores")
            ->select(['IdIndicador', 'Indicador', 'IdPertenencia', 'Informe'])
            ->orderBy('IdIndicador')
            ->get();

        $this->buildLegacyToNewMap($rows);

        $missing = [];
        foreach ($rows as $row) {
            $legacyId = (int) $row->IdIndicador;
            $categoryId = $this->legacyToNew[$legacyId] ?? null;
            if (! $categoryId || $categoryId < 1) {
                continue;
            }

            $informe = $this->cleanText((string) ($row->Informe ?? ''));
            if ($informe === '' || ! Str::endsWith(Str::lower($informe), '.pdf')) {
                continue;
            }

            $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $informe);
            $fullPath = $oldRoot.DIRECTORY_SEPARATOR.$relativePath;
            if (file_exists($fullPath)) {
                continue;
            }

            $name = $this->cleanText((string) $row->Indicador);
            if ($name === '') {
                $name = 'Registro '.$legacyId;
            }

            $missing[] = [
                'legacy_id' => $legacyId,
                'nombre' => $name,
                'informe_raw' => (string) $row->Informe,
                'informe_limpio' => $informe,
                'ruta_absoluta_esperada' => $fullPath,
                'category_id' => $categoryId,
            ];
        }

        if ($missing === []) {
            $this->info('No hay PDFs con Informe .pdf faltantes en disco (según la misma lógica que la migración).');

            return self::SUCCESS;
        }

        $this->info('PDFs no encontrados: '.count($missing));
        $this->newLine();

        if ($csv) {
            $this->line('legacy_id;category_id;nombre;ruta_pdf_relativa;ruta_absoluta');
            foreach ($missing as $m) {
                $nombre = str_replace(';', ',', $m['nombre']);
                $this->line(sprintf(
                    '%d;%d;%s;%s;%s',
                    $m['legacy_id'],
                    $m['category_id'],
                    $nombre,
                    str_replace(';', ',', $m['informe_limpio']),
                    str_replace(';', ',', $m['ruta_absoluta_esperada'])
                ));
            }

            return self::SUCCESS;
        }

        foreach ($missing as $m) {
            $this->line('────────────────────────────────────────────');
            $this->line('Legacy IdIndicador: '.$m['legacy_id']);
            $this->line('Category id (nuevo): '.$m['category_id']);
            $this->line('Nombre registro: '.$m['nombre']);
            $this->line('Informe (BD crudo): '.$m['informe_raw']);
            $this->line('Informe usado migración (limpio): '.$m['informe_limpio']);
            $this->line('Archivo esperado: '.$m['ruta_absoluta_esperada']);
        }

        return self::SUCCESS;
    }

    private function buildLegacyToNewMap($rows): void
    {
        $pending = collect($rows)->keyBy('IdIndicador');
        $lastPending = -1;

        while ($pending->isNotEmpty() && $pending->count() !== $lastPending) {
            $lastPending = $pending->count();

            foreach ($pending as $legacyId => $row) {
                $legacyId = (int) $legacyId;
                $parentLegacyId = $row->IdPertenencia ? (int) $row->IdPertenencia : null;
                $parentNewId = null;

                if ($parentLegacyId !== null) {
                    $parentNewId = $this->legacyToNew[$parentLegacyId] ?? null;
                    if ($parentNewId === null) {
                        continue;
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

                if (! $existing) {
                    continue;
                }

                $this->legacyToNew[$legacyId] = (int) $existing->id;
                $pending->forget($legacyId);
            }
        }
    }

    private function cleanText(string $value): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $decoded) ?? '');
    }
}
