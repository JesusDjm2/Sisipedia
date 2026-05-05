<?php

namespace App\Services;

use Google\Client;
use Google\Http\MediaFileUpload;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;

class GoogleDriveService
{
    private Drive $service;

    private Client $client;

    private array $folderIds = [
        'pdf' => null,
        'audio' => null,
        'video' => null,
    ];

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/google-credentials.json'));
        $this->client->addScope(Drive::DRIVE);

        $this->service = new Drive($this->client);

        $this->folderIds = [
            'pdf' => env('GOOGLE_DRIVE_FOLDER_PDF'),
            'doc' => env('GOOGLE_DRIVE_FOLDER_PDF'),  // docs van a la misma carpeta que pdf
            'audio' => env('GOOGLE_DRIVE_FOLDER_AUDIO'),
            'video' => env('GOOGLE_DRIVE_FOLDER_VIDEO'),
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
        $mimeType = $file->getMimeType() ?? 'application/octet-stream';

        $meta = new DriveFile([
            'name' => $fileName,
            'parents' => $folderId ? [$folderId] : [],
        ]);

        // Activar modo diferido para obtener la petición sin ejecutarla aún
        $this->client->setDefer(true);

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
}
