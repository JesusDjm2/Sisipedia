<?php

namespace App\Http\Controllers;

use App\Models\Canciones;
use App\Models\Libro;
use App\Models\sisipedia\Aportacion;
use App\Models\sisipedia\Category;
use App\Models\sisipedia\CategoryFile;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EnlacesController extends Controller
{
    /**
     * Umbral mínimo de similitud (similar_text de PHP, 0–100).
     * Ej.: "linderaj" vs "lindejare" ≈ 70,6 % — con 70 % sí aparece el registro.
     * Si subes a 90 %, parejas con pequeñas diferencias dejarían de coincidir.
     */
    private const FUZZY_SIMILARITY_THRESHOLD = 70.0;

    /** Patrón LIKE seguro e insensible a mayúsculas (SQL). */
    private static function sqlLikePattern(string $query): string
    {
        $t = mb_strtolower(trim($query));

        return '%'.addcslashes($t, '%_\\').'%';
    }

    private static function needleNorm(string $query): string
    {
        return mb_strtolower(trim($query));
    }

    /**
     * Mejor similitud entre la consulta y un texto largo:
     * coincidencia parcial (substring) = 100 %; si no, similar_text global y por palabras.
     */
    private static function fuzzyBestPercent(string $needleNorm, ?string $haystack): float
    {
        if ($haystack === null || $haystack === '') {
            return 0.0;
        }
        $hay = mb_strtolower(trim($haystack));
        $n = mb_strlen($needleNorm);
        if ($n < 1) {
            return 0.0;
        }
        if ($hay === '') {
            return 0.0;
        }
        if (str_contains($hay, $needleNorm)) {
            return 100.0;
        }

        similar_text($needleNorm, $hay, $pct);
        $best = (float) $pct;

        foreach (preg_split('/\s+/u', $hay) as $word) {
            if (mb_strlen($word) < 2) {
                continue;
            }
            similar_text($needleNorm, $word, $wp);
            if ((float) $wp > $best) {
                $best = (float) $wp;
            }
        }

        return $best;
    }

    /** ¿La consulta coincide por texto contenido o por similitud ≥ umbral? */
    private static function fieldMatches(string $needleNorm, ?string $field): bool
    {
        return self::fuzzyBestPercent($needleNorm, $field) >= self::FUZZY_SIMILARITY_THRESHOLD;
    }

    public function index()
    {
        $libros = Libro::latest()->take(8)->get();
        $videos = Video::with('categoria')->latest()->take(6)->get();

        $sisipediaVideoFiles = CategoryFile::query()
            ->where('tipo', 'video')
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->with(['category.parent.parent'])
            ->latest('updated_at')
            ->take(12)
            ->get();

        $sisipediaVideoAportaciones = Aportacion::query()
            ->where('is_approved', true)
            ->whereNotNull('video')
            ->with(['category.parent.parent'])
            ->latest()
            ->take(12)
            ->get();

        $sisipediaAudioFiles = CategoryFile::query()
            ->where('tipo', 'audio')
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->with(['category.parent.parent'])
            ->latest('updated_at')
            ->take(12)
            ->get();

        $sisipediaAudioAportaciones = Aportacion::query()
            ->where('is_approved', true)
            ->whereNotNull('audio')
            ->with(['category.parent.parent'])
            ->latest()
            ->take(12)
            ->get();

        return view('welcome', compact(
            'libros',
            'videos',
            'sisipediaVideoFiles',
            'sisipediaVideoAportaciones',
            'sisipediaAudioFiles',
            'sisipediaAudioAportaciones'
        ));
    }

    public function busqueda(Request $request)
    {
        $query = trim((string) $request->input('query', ''));

        if (mb_strlen($query) < 3) {
            return redirect()->route('index')->with(
                'busqueda_error',
                'Escribe al menos 3 caracteres para buscar.'
            );
        }

        $like = self::sqlLikePattern($query);
        $needle = self::needleNorm($query);

        $canciones = Canciones::cursor()->filter(function ($c) use ($needle) {
            return self::fieldMatches($needle, $c->nombre)
                || self::fieldMatches($needle, $c->autor);
        })->collect();

        $cancionesCount = $canciones->count();

        $videos = Video::cursor()->filter(function ($v) use ($needle) {
            return self::fieldMatches($needle, $v->nombre)
                || self::fieldMatches($needle, $v->descripcion ?? '');
        })->collect();

        $videosScored = self::scoreVideosFromCollection($videos, $needle);
        $videosCount = $videos->count();

        $libros = Libro::cursor()->filter(function ($l) use ($needle) {
            return self::fieldMatches($needle, $l->nombre)
                || self::fieldMatches($needle, $l->autor);
        })->collect();

        $librosCount = $libros->count();

        $sisipediaHits = $this->buildSisipediaHits($needle);
        $sisipediaCount = $sisipediaHits->count();

        $aportaciones = Aportacion::with('category.parent.parent')
            ->where('is_approved', true)
            ->cursor()
            ->filter(function ($a) use ($needle) {
                return self::fieldMatches($needle, $a->nombre_ol)
                    || self::fieldMatches($needle, $a->institucion)
                    || self::fieldMatches($needle, $a->ubicacion)
                    || self::fieldMatches($needle, $a->detalle);
            })
            ->take(80)
            ->collect();

        $aportacionesCount = $aportaciones->count();

        $hasResults = ! $canciones->isEmpty()
            || ! $videos->isEmpty()
            || ! $libros->isEmpty()
            || $sisipediaCount > 0
            || ! $aportaciones->isEmpty();

        return view('buscador', [
            'canciones' => $canciones,
            'videos' => $videos,
            'videosScored' => $videosScored,
            'libros' => $libros,
            'sisipediaHits' => $sisipediaHits,
            'aportaciones' => $aportaciones,
            'query' => $query,
            'hasResults' => $hasResults,
            'cancionesCount' => $cancionesCount,
            'videosCount' => $videosCount,
            'librosCount' => $librosCount,
            'sisipediaCount' => $sisipediaCount,
            'aportacionesCount' => $aportacionesCount,
            'fuzzyThreshold' => self::FUZZY_SIMILARITY_THRESHOLD,
        ]);
    }

    /**
     * @return Collection<int, array{model: Video, similarity: float, match_direct: bool}>
     */
    private static function scoreVideosFromCollection(Collection $videos, string $needle): Collection
    {
        return $videos->map(function (Video $v) use ($needle) {
            $simNombre = self::fuzzyBestPercent($needle, $v->nombre ?? '');
            $simDesc = self::fuzzyBestPercent($needle, $v->descripcion ?? '');
            $best = max($simNombre, $simDesc);
            $direct = str_contains(mb_strtolower((string) ($v->nombre ?? '')), $needle)
                || str_contains(mb_strtolower((string) ($v->descripcion ?? '')), $needle);

            return [
                'model' => $v,
                'similarity' => round($best, 1),
                'match_direct' => $direct,
            ];
        })->sortByDesc(fn (array $r) => $r['similarity'])->values();
    }

    /**
     * Registros Sisipedia: substring + similitud difusa en título, descripción y nombres de archivo.
     *
     * @return Collection<int, array{category: Category, similarity: float, match_direct: bool, sources: array<int, array<string, mixed>>}>
     */
    private function buildSisipediaHits(string $needle): Collection
    {
        $hits = [];

        $tipoColors = ['pdf' => 'danger', 'doc' => 'primary', 'audio' => 'success', 'video' => 'warning'];

        $categories = Category::where('is_active', true)
            ->with(['parent.parent.parent', 'files'])
            ->get();

        foreach ($categories as $cat) {
            $sources = [];
            $scores = [];
            $matchedBySubstring = false;

            $nameLower = mb_strtolower($cat->name);
            $nameDirect = str_contains($nameLower, $needle);
            $simName = self::fuzzyBestPercent($needle, $cat->name);

            if ($nameDirect) {
                $matchedBySubstring = true;
                $scores[] = 100.0;
                $sources[] = [
                    'kind' => 'direct',
                    'label' => 'Texto encontrado en el título',
                ];
            } elseif ($simName >= self::FUZZY_SIMILARITY_THRESHOLD) {
                $scores[] = $simName;
                $sources[] = [
                    'kind' => 'fuzzy',
                    'label' => 'Título · similitud '.round($simName, 1).' %',
                    'similarity' => round($simName, 1),
                ];
            }

            if ($cat->description) {
                $descLower = mb_strtolower($cat->description);
                $descDirect = str_contains($descLower, $needle);
                $simDesc = self::fuzzyBestPercent($needle, $cat->description);

                if ($descDirect) {
                    $matchedBySubstring = true;
                    $scores[] = 100.0;
                    $sources[] = [
                        'kind' => 'direct',
                        'label' => 'Texto encontrado en la descripción',
                    ];
                } elseif ($simDesc >= self::FUZZY_SIMILARITY_THRESHOLD) {
                    $scores[] = $simDesc;
                    $sources[] = [
                        'kind' => 'fuzzy',
                        'label' => 'Descripción · similitud '.round($simDesc, 1).' %',
                        'similarity' => round($simDesc, 1),
                    ];
                }
            }

            foreach ($cat->files as $file) {
                $labelText = $file->nombre_original ?: $file->nombre_display;
                $hay = $file->nombre_original ?? $file->nombre_display;
                $simFile = self::fuzzyBestPercent($needle, $hay);

                $tipoMatch = str_contains(mb_strtolower($file->tipo), $needle)
                    || self::fuzzyBestPercent($needle, $file->tipo) >= self::FUZZY_SIMILARITY_THRESHOLD;

                $fileDirect = ($file->nombre_original && str_contains(mb_strtolower($file->nombre_original), $needle));

                if ($fileDirect) {
                    $matchedBySubstring = true;
                    $scores[] = 100.0;
                    $sources[] = [
                        'kind' => 'archivo_direct',
                        'filename' => $file->nombre_display,
                        'tipo' => $file->tipo,
                        'badge' => $tipoColors[$file->tipo] ?? 'secondary',
                        'label' => 'Nombre de archivo',
                    ];
                } elseif ($simFile >= self::FUZZY_SIMILARITY_THRESHOLD) {
                    $scores[] = $simFile;
                    $sources[] = [
                        'kind' => 'archivo_fuzzy',
                        'filename' => $file->nombre_display,
                        'tipo' => $file->tipo,
                        'badge' => $tipoColors[$file->tipo] ?? 'secondary',
                        'label' => 'Archivo · ~'.round($simFile, 1).' % · '.$labelText,
                        'similarity' => round($simFile, 1),
                    ];
                } elseif ($tipoMatch && ! $file->nombre_original) {
                    $scores[] = max(60.0, self::fuzzyBestPercent($needle, $file->tipo));
                    $sources[] = [
                        'kind' => 'archivo_tipo',
                        'filename' => $file->nombre_display,
                        'tipo' => $file->tipo,
                        'badge' => $tipoColors[$file->tipo] ?? 'secondary',
                        'label' => 'Tipo de archivo',
                    ];
                }
            }

            if ($scores === []) {
                continue;
            }

            $best = max($scores);

            $hits[$cat->id] = [
                'category' => $cat,
                'similarity' => round($best, 1),
                'match_direct' => $matchedBySubstring,
                'sources' => $sources,
            ];
        }

        return collect($hits)
            ->sortByDesc(fn (array $h) => $h['similarity'])
            ->values();
    }

    public function admin()
    {
        $admins = User::all();

        return view('auth.admins.index', compact('admins'));
    }
}
