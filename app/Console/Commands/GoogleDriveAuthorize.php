<?php

namespace App\Console\Commands;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Console\Command;

class GoogleDriveAuthorize extends Command
{
    protected $signature = 'drive:authorize';
    protected $description = 'Genera el refresh token de OAuth2 para Google Drive';

    public function handle(): void
    {
        $clientId     = env('GOOGLE_OAUTH_CLIENT_ID');
        $clientSecret = env('GOOGLE_OAUTH_CLIENT_SECRET');

        if (! $clientId || ! $clientSecret) {
            $this->error('Faltan GOOGLE_OAUTH_CLIENT_ID o GOOGLE_OAUTH_CLIENT_SECRET en .env');
            return;
        }

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();

        $this->info('');
        $this->info('=== AUTORIZACIÓN GOOGLE DRIVE ===');
        $this->info('1. Abre este enlace en tu navegador:');
        $this->line('');
        $this->line($authUrl);
        $this->line('');
        $this->info('2. Inicia sesión con tu cuenta Google (la dueña del Drive)');
        $this->info('3. Copia el código que te muestre Google');
        $this->line('');

        $code = $this->ask('Pega aquí el código de autorización');

        $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
        $token = $client->fetchAccessTokenWithAuthCode(trim($code));

        if (isset($token['error'])) {
            $this->error('Error al obtener el token: ' . $token['error_description']);
            return;
        }

        $refreshToken = $token['refresh_token'] ?? null;

        if (! $refreshToken) {
            $this->error('No se recibió refresh_token. Asegúrate de haber usado prompt=consent.');
            return;
        }

        // Escribir en .env automáticamente
        $envPath    = base_path('.env');
        $envContent = file_get_contents($envPath);

        if (str_contains($envContent, 'GOOGLE_OAUTH_REFRESH_TOKEN=')) {
            $envContent = preg_replace(
                '/GOOGLE_OAUTH_REFRESH_TOKEN=.*/',
                'GOOGLE_OAUTH_REFRESH_TOKEN=' . $refreshToken,
                $envContent
            );
        } else {
            $envContent .= "\nGOOGLE_OAUTH_REFRESH_TOKEN={$refreshToken}\n";
        }

        file_put_contents($envPath, $envContent);

        $this->info('');
        $this->info('✓ Refresh token guardado en .env correctamente.');
        $this->info('✓ Google Drive listo para subir archivos.');

        // Verificar conexión
        $client->fetchAccessTokenWithRefreshToken($refreshToken);
        $drive   = new \Google\Service\Drive($client);
        $about   = $drive->about->get(['fields' => 'user,storageQuota']);
        $user    = $about->getUser();
        $quota   = $about->getStorageQuota();
        $usedGB  = round($quota->getUsage() / 1073741824, 2);
        $totalGB = $quota->getLimit() ? round($quota->getLimit() / 1073741824, 2) . ' GB' : 'ilimitado';

        $this->info('');
        $this->info("Cuenta: {$user->getDisplayName()} ({$user->getEmailAddress()})");
        $this->info("Almacenamiento usado: {$usedGB} GB / {$totalGB}");
    }
}
