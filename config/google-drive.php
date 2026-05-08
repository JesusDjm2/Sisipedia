<?php

return [

    /**
     * Carpeta referencia Sisipedia (diagnóstico / comandos Artisan).
     */
    'folder_sisipedia' => env('GOOGLE_DRIVE_FOLDER_SISIPEDIA'),

    /**
     * Carpeta destino por tipo de archivo.
     *
     * IMPORTANTE en producción: estas claves solo deben leerse vía config(),
     * para que funcionen con php artisan config:cache.
     */
    'folders' => [
        'pdf' => env('GOOGLE_DRIVE_FOLDER_PDF'),
        'doc' => env('GOOGLE_DRIVE_FOLDER_DOC') ?: env('GOOGLE_DRIVE_FOLDER_PDF'),
        'imagen' => env('GOOGLE_DRIVE_FOLDER_IMAGEN'),
        'audio' => env('GOOGLE_DRIVE_FOLDER_AUDIO'),
        'video' => env('GOOGLE_DRIVE_FOLDER_VIDEO'),
    ],

];
