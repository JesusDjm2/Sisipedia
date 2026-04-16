<?php

namespace Database\Seeders;

use App\Models\sisipedia\Category;
use App\Services\GoogleDriveService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class CategoryDriveSeeder extends Seeder
{
    public function run(): void
    {
        $drive = app(GoogleDriveService::class);

        $this->command->info('');
        $this->command->info('══════════════════════════════════════════');
        $this->command->info('   SEEDER — Categorías con Google Drive   ');
        $this->command->info('══════════════════════════════════════════');

        // ── Categorías padre (sin archivos) ───────────────────────────────

        // Limpiar registros de prueba anteriores
        $slugs = ['musica-andina', 'literatura-quechua', 'huayno', 'sikuri', 'danza-tijeras'];
        $drive = app(GoogleDriveService::class);
        Category::whereIn('slug', $slugs)->each(function ($cat) use ($drive) {
            foreach (['pdf', 'audio', 'video'] as $field) {
                if ($cat->$field) {
                    $drive->delete($cat->$field);
                }
            }
            $cat->delete();
        });

        $this->command->info('');
        $this->command->comment('Creando categorías padre...');

        $musica = Category::create([
            'name'        => 'Música Andina',
            'slug'        => 'musica-andina',
            'description' => 'Música tradicional de los Andes peruanos.',
            'order'       => 1,
            'is_active'   => true,
        ]);
        $this->command->line("  ✓ [{$musica->id}] {$musica->name}");

        $literatura = Category::create([
            'name'        => 'Literatura Quechua',
            'slug'        => 'literatura-quechua',
            'description' => 'Textos y poesía en idioma quechua.',
            'order'       => 2,
            'is_active'   => true,
        ]);
        $this->command->line("  ✓ [{$literatura->id}] {$literatura->name}");

        // ── Categorías hijo con archivos en Drive ─────────────────────────

        $this->command->info('');
        $this->command->comment('Subiendo archivos a Google Drive...');

        // 1. Hijo con PDF
        $pdfFile = $this->makeTempFile('huayno-letra.pdf', "Letra del Huayno Tradicional\nCancion de los Andes\nVerse 1: Wayra wayra...");
        $pdfId   = $drive->upload($pdfFile, 'pdf', 'huayno-letra.pdf');
        $this->command->line("  ✓ PDF subido → {$pdfId}");

        $cat1 = Category::create([
            'name'        => 'Huayno',
            'slug'        => 'huayno',
            'description' => 'Género musical andino con raíces prehispánicas.',
            'pdf'         => $pdfId,
            'parent_id'   => $musica->id,
            'order'       => 1,
            'is_active'   => true,
        ]);
        $this->command->line("  ✓ [{$cat1->id}] {$cat1->name} (PDF: {$pdfId})");

        // 2. Hijo con Audio
        $audioFile = $this->makeTempFile('sikuri-demo.mp3', str_repeat('AUDIO_DATA_SISIPEDIA ', 100));
        $audioId   = $drive->upload($audioFile, 'audio', 'sikuri-demo.mp3');
        $this->command->line("  ✓ Audio subido → {$audioId}");

        $cat2 = Category::create([
            'name'        => 'Sikuri',
            'slug'        => 'sikuri',
            'description' => 'Música de zampoñas en conjunto, típica del altiplano.',
            'audio'       => $audioId,
            'parent_id'   => $musica->id,
            'order'       => 2,
            'is_active'   => true,
        ]);
        $this->command->line("  ✓ [{$cat2->id}] {$cat2->name} (Audio: {$audioId})");

        // 3. Hijo con Video
        $videoFile = $this->makeTempFile('danza-tijeras.mp4', str_repeat('VIDEO_DATA_SISIPEDIA ', 200));
        $videoId   = $drive->upload($videoFile, 'video', 'danza-tijeras.mp4');
        $this->command->line("  ✓ Video subido → {$videoId}");

        $cat3 = Category::create([
            'name'        => 'Danza de Tijeras',
            'slug'        => 'danza-tijeras',
            'description' => 'Danza ritual declarada Patrimonio Inmaterial de la Humanidad.',
            'video'       => $videoId,
            'parent_id'   => $literatura->id,
            'order'       => 1,
            'is_active'   => true,
        ]);
        $this->command->line("  ✓ [{$cat3->id}] {$cat3->name} (Video: {$videoId})");

        // ── Resumen ───────────────────────────────────────────────────────

        $this->command->info('');
        $this->command->info('══════════════════════════════════════════');
        $this->command->info('  ✓ 5 categorías creadas correctamente');
        $this->command->info('  ✓ 3 archivos subidos a Google Drive');
        $this->command->info('══════════════════════════════════════════');
        $this->command->line('');
        $this->command->line('  Links para verificar en Drive:');
        $this->command->line('  PDF   → ' . \App\Services\GoogleDriveService::getUrl($pdfId));
        $this->command->line('  Audio → ' . \App\Services\GoogleDriveService::getUrl($audioId));
        $this->command->line('  Video → ' . \App\Services\GoogleDriveService::getUrl($videoId));
        $this->command->info('');
    }

    /**
     * Crea un archivo temporal y lo devuelve como UploadedFile.
     */
    private function makeTempFile(string $name, string $content): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'sisipedia_seed_');
        file_put_contents($tmp, $content);

        $mimes = [
            'pdf'  => 'application/pdf',
            'mp3'  => 'audio/mpeg',
            'mp4'  => 'video/mp4',
        ];
        $ext      = pathinfo($name, PATHINFO_EXTENSION);
        $mimeType = $mimes[$ext] ?? 'application/octet-stream';

        return new UploadedFile($tmp, $name, $mimeType, null, true);
    }
}
