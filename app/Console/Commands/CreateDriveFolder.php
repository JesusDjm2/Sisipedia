<?php

namespace App\Console\Commands;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Console\Command;

class CreateDriveFolder extends Command
{
    protected $signature = 'drive:create-folder
                            {name=imagenes : Nombre de la carpeta a crear}
                            {--parent= : ID de carpeta padre (opcional)}
                            {--env-key=GOOGLE_DRIVE_FOLDER_IMAGEN : Clave .env a actualizar con el ID creado}';

    protected $description = 'Crea una carpeta en Google Drive (Shared Drive) y actualiza .env con su ID';

    public function handle(): int
    {
        try {
            $client = new Client();
            $client->setAuthConfig(storage_path('app/google-credentials.json'));
            $client->addScope(Drive::DRIVE);
            $drive = new Drive($client);
        } catch (\Throwable $e) {
            $this->error('No se pudo inicializar Google Drive: '.$e->getMessage());

            return self::FAILURE;
        }

        $name = (string) $this->argument('name');
        $parentId = $this->option('parent');

        if (! $parentId) {
            $pdfFolder = config('google-drive.folders.pdf');
            if (! $pdfFolder) {
                $this->error('Falta GOOGLE_DRIVE_FOLDER_PDF y no se indicó --parent.');

                return self::FAILURE;
            }

            try {
                $pdfMeta = $drive->files->get($pdfFolder, [
                    'fields' => 'id,name,parents',
                    'supportsAllDrives' => true,
                ]);
                $parents = $pdfMeta->getParents();
                $parentId = $parents[0] ?? null;
            } catch (\Throwable $e) {
                $this->error('No se pudo leer la carpeta PDF: '.$e->getMessage());

                return self::FAILURE;
            }
        }

        if (! $parentId) {
            $this->error('No se encontró carpeta padre válida.');

            return self::FAILURE;
        }

        try {
            $query = sprintf(
                "name = '%s' and '%s' in parents and trashed = false and mimeType = 'application/vnd.google-apps.folder'",
                str_replace("'", "\\'", $name),
                $parentId
            );

            $existing = $drive->files->listFiles([
                'q' => $query,
                'fields' => 'files(id,name)',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ])->getFiles();

            if (! empty($existing)) {
                $folderId = $existing[0]->getId();
                $this->warn("La carpeta '{$name}' ya existe. ID: {$folderId}");
            } else {
                $folder = new DriveFile([
                    'name' => $name,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'parents' => [$parentId],
                ]);

                $created = $drive->files->create($folder, [
                    'fields' => 'id,name,webViewLink',
                    'supportsAllDrives' => true,
                ]);

                $folderId = $created->getId();
                $this->info("Carpeta creada: {$name}");
                $this->line("ID: {$folderId}");
            }
        } catch (\Throwable $e) {
            $this->error('Error creando/buscando carpeta: '.$e->getMessage());

            return self::FAILURE;
        }

        $envKey = (string) $this->option('env-key');
        if ($envKey !== '') {
            $ok = $this->setEnvValue($envKey, $folderId);
            if ($ok) {
                $this->info("Actualizado .env: {$envKey}={$folderId}");
            } else {
                $this->warn("No se pudo actualizar .env automáticamente. Usa manualmente: {$envKey}={$folderId}");
            }
        }

        $this->line('Ejecuta: php artisan config:clear');

        return self::SUCCESS;
    }

    private function setEnvValue(string $key, string $value): bool
    {
        $path = base_path('.env');
        if (! file_exists($path) || ! is_writable($path)) {
            return false;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return false;
        }

        $pattern = "/^".preg_quote($key, '/')."=.*/m";
        $line = $key.'='.$value;

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, $line, $content);
        } else {
            $newContent = rtrim($content).PHP_EOL.$line.PHP_EOL;
        }

        return file_put_contents($path, $newContent) !== false;
    }
}
