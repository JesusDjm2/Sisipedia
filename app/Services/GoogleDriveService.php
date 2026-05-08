<?php

namespace App\Services;

use Google\Client;
use Google\Http\MediaFileUpload;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use Psr\Http\Message\RequestInterface;

class GoogleDriveService
{
    private Drive $service;

    private Client $client;

    private array $folderIds = [
        'pdf' => null,
        'imagen' => null,
        'audio' => null,
        'video' => null,
    ];

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/google-credentials.json'));
        $this->client->addScope(Drive::DRIVE);

        $this->service = new Drive($this->client);

        $folders = config('google-drive.folders', []);
        $this->folderIds = [
            'pdf' => $folders['pdf'] ?? null,
            'doc' => $folders['doc'] ?? ($folders['pdf'] ?? null),
            'imagen' => $folders['imagen'] ?? null,
            'audio' => $folders['audio'] ?? null,
            'video' => $folders['video'] ?? null,
        ];
    }

    // Tamaño de cada chunk: 5 MB (mínimo requerido por Google)
    private const CHUNK_SIZE = 5 * 1024 * 1024;

    /**
     * Sube un archivo a la carpeta correspondiente en Drive (resumable upload por chunks).
     * Devuelve el file ID de Drive.
     */
    public function upload(UploadedFile $file, string $type, string $fileName): string
    {
        $folderId = $this->folderIds[$type] ?? null;
        // Word usa la carpeta PDF; si en producción faltaba la clave «doc», caía sin parents → My Drive de la SA → 403 quota.
        if (empty($folderId) && $type === 'doc') {
            $folderId = $this->folderIds['pdf'] ?? null;
        }
        $mimeType = $file->getMimeType() ?? 'application/octet-stream';

        $meta = new DriveFile([
            'name' => $fileName,
            'parents' => $folderId ? [$folderId] : [],
        ]);

        // Activar modo diferido para obtener la petición sin ejecutarla aún
        $this->client->setDefer(true);

        /** @var RequestInterface $request Petición HTTP cuando el cliente está en modo defer (no un DriveFile). */
        $request = $this->service->files->create($meta, [
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);

        // Configurar la subida resumible por chunks
        $media = new MediaFileUpload(
            $this->client,
            $request,
            $mimeType,
            null,
            true,               // resumable
            self::CHUNK_SIZE
        );
        $media->setFileSize($file->getSize());

        // Enviar chunks
        $handle = fopen($file->getRealPath(), 'rb');
        $uploaded = false;

        while (! $uploaded && ! feof($handle)) {
            $chunk = fread($handle, self::CHUNK_SIZE);
            $uploaded = $media->nextChunk($chunk);
        }

        fclose($handle);

        // Restaurar modo normal
        $this->client->setDefer(false);

        $fileId = $uploaded->getId();
        $this->makePublic($fileId);

        return $fileId;
    }

    /**
     * Elimina un archivo de Drive por su ID.
     */
    public function delete(string $fileId): void
    {
        try {
            $this->service->files->delete($fileId, ['supportsAllDrives' => true]);
        } catch (\Exception $e) {
            // Si el archivo ya no existe, no lanzar error
        }
    }

    /**
     * Da permisos de lectura pública al archivo.
     */
    private function makePublic(string $fileId): void
    {
        $permission = new \Google\Service\Drive\Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);
        $this->service->permissions->create($fileId, $permission, ['supportsAllDrives' => true]);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public static function getUrl(string $fileId): string
    {
        return "https://drive.google.com/file/d/{$fileId}/view";
    }

    public static function getEmbedUrl(string $fileId): string
    {
        return "https://drive.google.com/uc?export=download&id={$fileId}";
    }

    public static function getPreviewUrl(string $fileId): string
    {
        return "https://drive.google.com/file/d/{$fileId}/preview";
    }

    public static function getThumbnailUrl(string $fileId, int $size = 120): string
    {
        return "https://drive.google.com/thumbnail?id={$fileId}&sz=w{$size}";
    }
}
