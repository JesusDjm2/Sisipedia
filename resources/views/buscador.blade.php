@extends('layouts.padre')
@section('titulo', '- Página de Inicio')
@section('contenido')
    <div class="fixed-image-container">
        <img src="{{ asset('img/biblioteca-muestra.jpg') }}" alt="">
        <div class="overlay"></div>
        <div class="centered-content px-3">
            <h2 class="mb-2">EESP Pukllasunchis</h2>
            <h1 class="h3 mb-3">Resultados para <span class="text-white opacity-75">“{{ $query }}”</span></h1>
            @if (!$hasResults)
                <p class="mb-0">Sin resultados.</p>
            @else
                @php
                    $resultados = [];
                    if ($sisipediaCount > 0) {
                        $resultados[] =
                            $sisipediaCount . ' ' . ($sisipediaCount === 1 ? 'registro Sisipedia' : 'registros Sisipedia');
                    }
                    if ($videosCount > 0) {
                        $resultados[] = $videosCount . ' ' . ($videosCount === 1 ? 'video' : 'videos');
                    }
                    if ($librosCount > 0) {
                        $resultados[] = $librosCount . ' ' . ($librosCount === 1 ? 'texto' : 'textos');
                    }
                    if ($aportacionesCount > 0) {
                        $resultados[] =
                            $aportacionesCount .
                            ' ' .
                            ($aportacionesCount === 1 ? 'aportación' : 'aportaciones');
                    }
                    if ($cancionesCount > 0) {
                        $resultados[] =
                            $cancionesCount . ' ' . ($cancionesCount === 1 ? 'canción' : 'canciones');
                    }
                @endphp
                <p class="mb-0 mt-2 small">
                    Se encontraron: {{ implode(' · ', $resultados) }}
                </p>
            @endif
        </div>
    </div>

    <div class="container mt-4 mb-5 contenedorContenido">
        <div class="alert alert-light border shadow-sm rounded-3 small mb-4" role="note">
            <i class="fas fa-search text-primary me-2"></i>
            Búsqueda <strong>sin distinguir mayúsculas</strong>. Además se usa <strong>similitud de texto</strong>
            (<code>similar_text</code> de PHP): si escribes con un error tipográfico respecto al registro
            (p. ej. “linderaj” frente a “lindejare”), puede aparecer igual si la similitud es al menos
            <strong>{{ number_format($fuzzyThreshold ?? 70, 0) }}&nbsp;%</strong>.
            El número en cada tarjeta es esa <strong>similitud estimada</strong> (no un porcentaje estadístico de
            encuestas).
        </div>

        @if (!$hasResults)
            <div class="row">
                <div class="col-lg-12">
                    <p>No hay coincidencias para <strong class="text-primary">“{{ $query }}”</strong>. Prueba con
                        otras palabras o revisa la ortografía.</p>
                    <form action="{{ route('busqueda') }}" method="GET" class="search-form mb-4">
                        <div class="input-group mb-3 shadow-sm rounded-3 overflow-hidden" style="max-width: 36rem;">
                            <input type="text" class="form-control border-secondary-subtle" name="query"
                                placeholder="Nueva búsqueda…" aria-label="Buscar de nuevo" minlength="3"
                                maxlength="120">
                            <button class="btn btn-primary px-4" type="submit">Buscar</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="row mb-2 g-4">

            {{-- ── Sisipedia (registro + archivos adjuntos) ─────────────── --}}
            @if ($sisipediaHits->isNotEmpty())
                <div class="col-12">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge bg-primary rounded-pill">{{ $sisipediaCount }}</span>
                        <h3 class="h5 mb-0 fw-bold">Registros Sisipedia</h3>
                        <span class="badge rounded-pill bg-light text-secondary border small fw-normal">Título,
                            descripción o nombre de archivo</span>
                    </div>
                    <p class="text-muted small mb-3">Incluye coincidencias en los archivos PDF, Word, audio o video
                        adjuntos a cada registro.</p>
                </div>
                @foreach ($sisipediaHits as $hit)
                    @php
                        $reg = $hit['category'];
                        $path = [];
                        $cur = $reg;
                        while ($cur) {
                            array_unshift($path, $cur->name);
                            $cur = $cur->parent ?? null;
                        }
                    @endphp
                    <div class="col-lg-4 col-md-6">
                        <a href="{{ route('sisipedia.categories.show', $reg) }}" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm sisipedia-hit-card position-relative">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="d-flex align-items-start gap-2 min-w-0">
                                            @if ($reg->image)
                                                <img src="{{ asset($reg->image) }}" class="rounded-circle flex-shrink-0"
                                                    width="40" height="40" style="object-fit:cover;" alt="">
                                            @else
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width:40px;height:40px;min-width:40px;">
                                                    <i class="fa fa-folder-open"></i>
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-grow-1">
                                                <h6 class="mb-1 text-dark fw-semibold text-break">{{ $reg->name }}</h6>
                                                @if (count($path) > 1)
                                                    <small class="text-muted d-block buscador-card-breadcrumb">{{ implode(' › ', $path) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                        @if ($hit['match_direct'] ?? false)
                                            <span class="badge rounded-pill bg-success bg-opacity-15 text-success border border-success flex-shrink-0 tiny-rel-badge"
                                                title="Tu texto aparece dentro del campo">Directa</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary bg-opacity-15 text-secondary border flex-shrink-0 tiny-rel-badge"
                                                title="Similitud estimada (error tipográfico / variante)">~{{ $hit['similarity'] }}%</span>
                                        @endif
                                    </div>

                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        @foreach ($hit['sources'] as $src)
                                            @php $k = $src['kind'] ?? ''; @endphp
                                            @if ($k === 'direct')
                                                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary"
                                                    style="font-size:.68rem;">
                                                    <i class="fa fa-bookmark me-1"></i>{{ $src['label'] }}
                                                </span>
                                            @elseif($k === 'fuzzy')
                                                <span class="badge rounded-pill bg-info bg-opacity-10 text-info border border-info"
                                                    style="font-size:.68rem;">
                                                    <i class="fa fa-spell-check me-1"></i>{{ $src['label'] }}
                                                </span>
                                            @elseif(in_array($k, ['archivo_direct', 'archivo_fuzzy', 'archivo_tipo'], true))
                                                <span class="badge rounded-pill bg-{{ $src['badge'] ?? 'secondary' }} bg-opacity-10 text-{{ $src['badge'] ?? 'secondary' }} border border-{{ $src['badge'] ?? 'secondary' }}"
                                                    style="font-size:.68rem;">
                                                    <i class="fa fa-paperclip me-1"></i>{{ $src['filename'] }}
                                                    @if ($k === 'archivo_fuzzy')
                                                        <span class="opacity-75">(~{{ $src['similarity'] }}%)</span>
                                                    @endif
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if ($reg->description)
                                        <p class="buscador-detalle mb-3">{{ Str::limit($reg->description, 360) }}</p>
                                    @endif

                                    @php $regFiles = $reg->files ?? collect(); @endphp
                                    <div class="d-flex flex-wrap gap-1 pt-1 border-top border-light mt-auto">
                                        @if ($regFiles->where('tipo', 'pdf')->isNotEmpty())
                                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger"
                                                style="font-size:.6rem;">
                                                <i class="fa fa-file-pdf me-1"></i>PDF
                                            </span>
                                        @endif
                                        @if ($regFiles->where('tipo', 'doc')->isNotEmpty())
                                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary"
                                                style="font-size:.6rem;">
                                                <i class="fa fa-file-word me-1"></i>Doc
                                            </span>
                                        @endif
                                        @if ($regFiles->where('tipo', 'audio')->isNotEmpty())
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success"
                                                style="font-size:.6rem;">
                                                <i class="fa fa-music me-1"></i>Audio
                                            </span>
                                        @endif
                                        @if ($regFiles->where('tipo', 'video')->isNotEmpty())
                                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary"
                                                style="font-size:.6rem;">
                                                <i class="fa fa-video me-1"></i>Video
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            @endif

            {{-- ── Videos institucionales ───────────────────────────────── --}}
            @if ($videosScored->isNotEmpty())
                <div class="col-12 mt-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge bg-danger bg-opacity-15 text-danger rounded-pill border border-danger">{{ $videosCount }}</span>
                        <h3 class="h5 mb-0 fw-bold">Videos</h3>
                        <span class="badge rounded-pill bg-light text-secondary border small fw-normal">Nombre o
                            descripción del video</span>
                    </div>
                </div>
                @foreach ($videosScored as $row)
                    @php
                        $video = $row['model'];
                        $sim = $row['similarity'];
                        $vDir = $row['match_direct'] ?? false;
                    @endphp
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100 video-hit-card d-flex flex-column">
                            @if ($video->youtube)
                                <div class="video-thumbnail position-relative flex-shrink-0 rounded-top overflow-hidden"
                                    style="width:100%;height:200px;">
                                    <img loading="lazy" src="https://img.youtube.com/vi/{{ $video->youtube }}/hqdefault.jpg"
                                        alt="" class="w-100 h-100 object-fit-cover">
                                    <a href="https://www.youtube.com/watch?v={{ $video->youtube }}" target="_blank"
                                        rel="noopener" class="position-absolute top-50 start-50 translate-middle">
                                        <i class="fa-brands fa-youtube" style="color:red;font-size:56px;"></i>
                                    </a>
                                </div>
                            @elseif($video->drive)
                                <div class="ratio ratio-16x9 flex-shrink-0 bg-dark rounded-top overflow-hidden">
                                    <iframe loading="lazy"
                                        src="https://drive.google.com/file/d/{{ $video->drive }}/preview"
                                        class="border-0" allowfullscreen title="Video"></iframe>
                                </div>
                            @endif
                            <div class="card-body p-3 flex-grow-1 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <h6 class="mb-0 text-dark fw-semibold text-break">{{ $video->nombre }}</h6>
                                    @if ($vDir)
                                        <span class="badge rounded-pill bg-success bg-opacity-15 text-success border border-success flex-shrink-0 tiny-rel-badge">Directa</span>
                                    @else
                                        <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger flex-shrink-0 tiny-rel-badge">~{{ $sim }}%</span>
                                    @endif
                                </div>
                                @if ($video->descripcion)
                                    <p class="buscador-detalle mb-0">{{ Str::limit($video->descripcion, 320) }}</p>
                                @else
                                    <p class="text-muted small fst-italic mb-0">Sin descripción.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- ── Biblioteca (libros) ─────────────────────────────────────── --}}
            @if ($libros->isNotEmpty())
                <div class="col-12 mt-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge bg-success bg-opacity-15 text-success rounded-pill border border-success">{{ $librosCount }}</span>
                        <h3 class="h5 mb-0 fw-bold">Biblioteca</h3>
                        <span class="badge rounded-pill bg-light text-secondary border small fw-normal">Textos — título o
                            autor</span>
                    </div>
                </div>
                @foreach ($libros as $libro)
                    <div class="col-lg-3 mb-4 cardLibros" data-nombre="{{ $libro->nombre }}"
                        data-autor="{{ $libro->autor }}">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="text-center mb-2">{{ $libro->nombre }}</h5>
                                <p class="text-center text-muted small mb-3"><i class="fa fa-user me-1"></i>{{ $libro->autor }}
                                </p>
                                @php
                                    $idDocumento = $libro->identificador;
                                    $urlVistaPrevia = "https://drive.google.com/thumbnail?id={$idDocumento}&sz=w1000";
                                @endphp
                                <div class="contenedorLibro">
                                    <div class="contenedorImagen">
                                        <img class="vistaPrevia" src="{{ $urlVistaPrevia }}" alt="" loading="lazy">
                                        <div class="overlay">
                                            <a href="https://drive.google.com/file/d/{{ $libro->identificador }}/preview"
                                                target="_blank" rel="noopener">
                                                <button type="button" class="btnAbrirPDF">Abrir PDF</button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- ── Aportaciones ──────────────────────────────────────────── --}}
            @if ($aportaciones->isNotEmpty())
                @php
                    $rolColors = [
                        'Equipo Puklla' => '#6f42c1',
                        'Docente' => '#0d6efd',
                        'Líder' => '#198754',
                        'Niño/Estudiante' => '#fd7e14',
                    ];
                    $rolIcons = [
                        'Equipo Puklla' => 'fa-users',
                        'Docente' => 'fa-graduation-cap',
                        'Líder' => 'fa-star',
                        'Niño/Estudiante' => 'fa-child',
                    ];
                @endphp
                <div class="col-12 mt-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge bg-warning text-dark rounded-pill">{{ $aportacionesCount }}</span>
                        <h3 class="h5 mb-0 fw-bold">Aportaciones</h3>
                        <span class="badge rounded-pill bg-light text-secondary border small fw-normal">Texto de la
                            aportación</span>
                    </div>
                </div>
                @foreach ($aportaciones as $ap)
                    @php
                        $color = $rolColors[$ap->rol_nombre] ?? '#6c757d';
                        $icon = $rolIcons[$ap->rol_nombre] ?? 'fa-user';
                        $catPath = [];
                        $cur = $ap->category;
                        while ($cur) {
                            array_unshift($catPath, $cur->name);
                            $cur = $cur->parent ?? null;
                        }
                    @endphp
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-0 shadow-sm" style="border-left:4px solid {{ $color }} !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0 text-white"
                                        style="width:40px;height:40px;min-width:40px;background:{{ $color }};">
                                        <i class="fa {{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark fw-semibold">{{ $ap->nombre_ol }}</h6>
                                        <span class="badge rounded-pill text-white mt-1" style="background:{{ $color }};font-size:.65rem;">
                                            {{ $ap->rol_nombre }}
                                        </span>
                                    </div>
                                </div>
                                @if ($ap->category)
                                    <a href="{{ route('sisipedia.categories.show', $ap->category) }}"
                                        class="text-muted text-decoration-none d-block mb-2" style="font-size:.8rem;">
                                        <i class="fa fa-folder-open me-1"></i>{{ implode(' › ', $catPath) }}
                                    </a>
                                @endif
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @if ($ap->institucion)
                                        <span class="badge bg-light text-dark border" style="font-size:.65rem;">
                                            <i class="fa fa-building me-1"></i>{{ $ap->institucion }}
                                        </span>
                                    @endif
                                    @if ($ap->ubicacion)
                                        <span class="badge bg-light text-dark border" style="font-size:.65rem;">
                                            <i class="fa fa-map-marker me-1"></i>{{ $ap->ubicacion }}
                                        </span>
                                    @endif
                                </div>
                                @if ($ap->detalle)
                                    <p class="buscador-detalle mb-2">{{ Str::limit($ap->detalle, 320) }}</p>
                                @endif
                                <div class="d-flex flex-wrap gap-1">
                                    @if ($ap->pdf)
                                        <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger" style="font-size:.65rem;">
                                            <i class="fa fa-file-pdf-o me-1"></i>PDF
                                        </span>
                                    @endif
                                    @if ($ap->doc)
                                        <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size:.65rem;">
                                            <i class="fa fa-file-word-o me-1"></i>Doc
                                        </span>
                                    @endif
                                    @if ($ap->audio)
                                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success" style="font-size:.65rem;">
                                            <i class="fa fa-music me-1"></i>Audio
                                        </span>
                                    @endif
                                    @if ($ap->video)
                                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary" style="font-size:.65rem;">
                                            <i class="fa fa-video-camera me-1"></i>Video
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- ── Canciones ──────────────────────────────────────────────── --}}
            @if ($canciones->isNotEmpty())
                <div class="col-12 mt-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge rounded-pill bg-dark">{{ $cancionesCount }}</span>
                        <h3 class="h5 mb-0 fw-bold">Canciones</h3>
                        <span class="badge rounded-pill bg-light text-secondary border small fw-normal">Nombre o
                            autor</span>
                    </div>
                </div>
                @foreach ($canciones as $cancion)
                    @if ($cancion->youtube)
                        <div class="col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-7">
                                            <h5 class="card-title">{{ $cancion->nombre }}</h5>
                                            <p class="card-text mb-2 text-muted small">{{ $cancion->autor }}</p>
                                        </div>
                                        <div class="col-5 d-flex align-items-center justify-content-center">
                                            <img src="{{ asset('img/min/Youtube-Logo.webp') }}" loading="lazy" width="110" alt="">
                                        </div>
                                    </div>
                                    <div class="video-thumbnail position-relative" style="width:100%;height:220px;">
                                        <img loading="lazy" src="https://img.youtube.com/vi/{{ $cancion->youtube }}/hqdefault.jpg" alt=""
                                            class="w-100 h-100 object-fit-cover">
                                        <a href="https://www.youtube.com/watch?v={{ $cancion->youtube }}" target="_blank" rel="noopener"
                                            class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fa-brands fa-youtube" style="color:red;font-size:60px;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($cancion->drive)
                        <div class="col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-7">
                                            <h5 class="card-title">{{ $cancion->nombre }}</h5>
                                            <p class="card-text mb-2 text-muted small">{{ $cancion->autor }}</p>
                                        </div>
                                        <div class="col-5 d-flex align-items-center justify-content-center">
                                            <img src="{{ asset('img/min/google-drive-min.webp') }}" width="88" alt="">
                                        </div>
                                    </div>
                                    <div class="drive d-flex justify-content-center align-items-center rounded-2 overflow-hidden"
                                        style="height:135px;background:url('{{ asset('img/min/FONDO-DRIVE.webp') }}');background-size:cover;">
                                        <iframe src="https://drive.google.com/file/d/{{ $cancion->drive }}/preview"
                                            loading="lazy" allowfullscreen width="100%" height="80" title="Audio"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($cancion->spotify)
                        <div class="col-lg-4 mb-4">
                            <iframe style="border-radius:12px" loading="lazy"
                                src="https://open.spotify.com/embed/track/{{ $cancion->spotify }}" width="100%"
                                height="302" title="Spotify"></iframe>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>

    <style>
        .sisipedia-hit-card {
            border-left: 4px solid #0d6efd !important;
            transition: box-shadow .15s ease, transform .15s ease;
        }

        .sisipedia-hit-card:hover {
            box-shadow: 0 .35rem 1rem rgba(13, 110, 253, .18) !important;
        }

        .tiny-rel-badge {
            font-size: .68rem;
            font-weight: 600;
        }

        .bg-gradient {
            background: linear-gradient(135deg, #0d6efd, #6610f2) !important;
        }

        /* Detalle legible en tarjetas (antes truncado / ilegible) */
        .buscador-detalle {
            font-size: 0.9rem;
            line-height: 1.55;
            color: #495057;
            word-break: break-word;
        }

        .buscador-card-breadcrumb {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
            line-height: 1.35;
            word-break: break-word;
        }

        .video-hit-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-top: 3px solid rgba(220, 53, 69, 0.4);
        }
    </style>
@endsection
