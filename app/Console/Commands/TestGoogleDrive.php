<?php

namespace App\Console\Commands;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Console\Command;

class TestGoogleDrive extends Command
{
    protected $signature   = 'drive:test';
    protected $description = 'Diagnostica la conexión con Google Drive (Service Account)';

    private array $folderIds = [];
    private Drive  $drive;
    private Client $client;

    public function handle(): int
    {
        $this->line('');
        $this->info('════════════════════════════════════════════════');
        $this->info('   DIAGNÓSTICO GOOGLE DRIVE — SERVICE ACCOUNT   ');
        $this->info('════════════════════════════════════════════════');

        if (! $this->checkCredentialsFile())  return 1;
        if (! $this->checkAuthentication())   return 1;
        $this->checkFolders();
        $this->testUpload();

        $this->line('');
        return 0;
    }

    // ── 1. Archivo de credenciales ─────────────────────────────────────────

    private function checkCredentialsFile(): bool
    {
        $this->line('');
        $this->comment('[ 1 ] Archivo de credenciales');

        $path = storage_path('app/google-credentials.json');

        if (! file_exists($path)) {
            $this->error("  ✗ No existe: {$path}");
            return false;
        }

        $json = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('  ✗ El archivo no es JSON válido.');
            return false;
        }

        $type  = $json['type']         ?? 'FALTA';
        $email = $json['client_email'] ?? 'FALTA';

        $this->line("  Ruta  : {$path}");
        $this->line("  Tipo  : {$type}");
        $this->line("  Email : {$email}");

        if ($type !== 'service_account') {
            $this->error('  ✗ El tipo debe ser "service_account".');
            return false;
        }

        if (empty($json['private_key'])) {
            $this->error('  ✗ Falta "private_key" en el JSON.');
            return false;
        }

        $this->info('  ✓ Archivo válido');
        return true;
    }

    // ── 2. Autenticación con Google API ────────────────────────────────────

    private function checkAuthentication(): bool
    {
        $this->line('');
        $this->comment('[ 2 ] Autenticación con Google API');

        try {
            $this->client = new Client();
            $this->client->setAuthConfig(storage_path('app/google-credentials.json'));
            $this->client->addScope(Drive::DRIVE);
            $this->drive = new Drive($this->client);

            // Llamada mínima: obtener info de la propia service account
            $about = $this->drive->about->get(['fields' => 'user,storageQuota']);
            $user  = $about->getUser();
            $quota = $about->getStorageQuota();

            $this->info('  ✓ Autenticación correcta');
            $this->line('  Usuario  : ' . $user->getEmailAddress());
            $this->line('  Uso      : ' . round($quota->getUsage() / 1048576, 2) . ' MB');
            $this->line('  Cuota    : ' . ($quota->getLimit() ? round($quota->getLimit() / 1073741824, 2) . ' GB' : 'Sin cuota propia (service account)'));

            if (! $quota->getLimit()) {
                $this->warn('  ⚠ Las Service Accounts NO tienen cuota propia.');
                $this->warn('    Solo pueden subir a Shared Drives (Unidades Compartidas).');
            }

        } catch (\Exception $e) {
            $this->error('  ✗ Error de autenticación: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    // ── 3. Verificar carpetas configuradas ─────────────────────────────────

    private function checkFolders(): void
    {
        $this->line('');
        $this->comment('[ 3 ] Verificar carpetas en config/google-drive (.env compilado con config:cache)');

        $f = config('google-drive.folders', []);
        $this->folderIds = [
            'sisipedia' => config('google-drive.folder_sisipedia'),
            'pdf'       => $f['pdf'] ?? null,
            'doc'       => $f['doc'] ?? null,
            'imagen'    => $f['imagen'] ?? null,
            'audio'     => $f['audio'] ?? null,
            'video'     => $f['video'] ?? null,
        ];

        foreach ($this->folderIds as $name => $id) {
            if (empty($id)) {
                $this->warn("  ⚠ {$name}: ID vacío en .env");
                continue;
            }

            try {
                $file = $this->drive->files->get($id, [
                    'fields'            => 'id,name,mimeType,driveId,capabilities',
                    'supportsAllDrives' => true,
                ]);

                $isShared  = ! empty($file->getDriveId());
                $canUpload = $file->getCapabilities() ? $file->getCapabilities()->getCanAddChildren() : 'desconocido';
                $tipo      = $isShared ? 'Shared Drive ✓' : 'My Drive (carpeta normal)';

                $this->line("  {$name} ({$id})");
                $this->line("    Nombre    : {$file->getName()}");
                $this->line("    Tipo      : {$tipo}");
                $this->line("    Puede subir archivos: " . ($canUpload === true ? 'sí' : ($canUpload === false ? 'NO' : $canUpload)));

                if (! $isShared) {
                    $this->error("    ✗ Esta carpeta es de My Drive. La service account NO puede subir aquí.");
                    $this->error("      Debes crear una Unidad Compartida (Shared Drive) y mover esta carpeta.");
                }

            } catch (\Google\Service\Exception $e) {
                $body = json_decode($e->getMessage(), true);
                $code = $body['error']['code']    ?? $e->getCode();
                $msg  = $body['error']['message'] ?? $e->getMessage();
                $this->error("  ✗ {$name} ({$id}): [{$code}] {$msg}");
            }
        }
    }

    // ── 4. Prueba de subida real ────────────────────────────────────────────

    private function testUpload(): void
    {
        $this->line('');
        $this->comment('[ 4 ] Prueba de subida a carpeta PDF');

        $folderId = $this->folderIds['pdf'] ?? null;

        if (empty($folderId)) {
            $this->warn('  ⚠ GOOGLE_DRIVE_FOLDER_PDF no está definido. Saltando prueba.');
            return;
        }

        // Crear archivo de prueba temporal
        $tmpPath = tempnam(sys_get_temp_dir(), 'sisipedia_test_') . '.txt';
        file_put_contents($tmpPath, 'Archivo de prueba Sisipedia — ' . now()->toDateTimeString());

        try {
            $meta = new DriveFile([
                'name'    => 'sisipedia-test-' . time() . '.txt',
                'parents' => [$folderId],
            ]);

            $uploaded = $this->drive->files->create($meta, [
                'data'              => file_get_contents($tmpPath),
                'mimeType'          => 'text/plain',
                'uploadType'        => 'multipart',
                'fields'            => 'id,name,webViewLink',
                'supportsAllDrives' => true,
            ]);

            $fileId = $uploaded->getId();
            $this->info("  ✓ Archivo subido correctamente");
            $this->line("  ID    : {$fileId}");
            $this->line("  Link  : {$uploaded->getWebViewLink()}");

            // Intentar hacer público
            try {
                $perm = new Permission(['type' => 'anyone', 'role' => 'reader']);
                $this->drive->permissions->create($fileId, $perm, ['supportsAllDrives' => true]);
                $this->info("  ✓ Permisos públicos asignados");
            } catch (\Exception $e) {
                $this->warn("  ⚠ No se pudo hacer público: " . $e->getMessage());
                $this->warn("    (Puede requerirse configuración en Google Workspace)");
            }

            // Limpiar archivo de prueba de Drive
            $this->drive->files->delete($fileId, ['supportsAllDrives' => true]);
            $this->line("  (Archivo de prueba eliminado de Drive)");

        } catch (\Google\Service\Exception $e) {
            $body = json_decode($e->getMessage(), true);
            $code = $body['error']['code']    ?? $e->getCode();
            $msg  = $body['error']['message'] ?? $e->getMessage();
            $reason = $body['error']['errors'][0]['reason'] ?? '';

            $this->error("  ✗ Error al subir [{$code}]: {$msg}");

            if ($reason === 'storageQuotaExceeded') {
                $this->line('');
                $this->error('  CAUSA RAÍZ: La carpeta PDF es de My Drive, no Shared Drive.');
                $this->line('  SOLUCIÓN:');
                $this->line('    1. En Google Drive → "Unidades compartidas" → Crear nueva');
                $this->line('    2. Crear carpetas pdf / audio / video dentro');
                $this->line('    3. Agregar la service account como miembro (Colaborador)');
                $this->line('    4. Actualizar IDs en .env');
            }
        } finally {
            @unlink($tmpPath);
        }
    }
}
