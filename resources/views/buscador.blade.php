@extends('layouts.padre')
@section('titulo', '- Página de Inicio')
@section('contenido')
@php
    $hl = function(?string $text, string $q): string {
        $text = (string) ($text ?? '');
        if ($text === '' || $q === '') return e($text);
        $parts = preg_split('/(' . preg_quote($q, '/') . ')/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false || $parts === []) return e($text);
        $out = '';
        foreach ($parts as $i => $part) {
            $out .= $i % 2 === 1
                ? '<mark class="buscador-hl">' . e($part) . '</mark>'
                : e($part);
        }
        return $out;
    };
@endphp
    <div class="fixed-image-container">
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
                        <span class="badge bg-primary text-white rounded-pill">{{ $sisipediaCount }}</span>
                        <h3 class="h5 mb-0 fw-bold">Registros Sisipedia</h3>
                    </div>
                    <p class="text-muted small mb-3">Coincidencias en título, descripción y archivos adjuntos.</p>
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
                        <div class="card h-100 border-0 shadow-sm sisipedia-hit-card position-relative d-flex flex-column">
                            <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-start gap-2 mb-2">
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
                                        <h6 class="mb-1 text-dark fw-semibold text-break">
                                            <a href="{{ route('sisipedia.categories.show', $reg) }}" class="text-decoration-none text-dark">
                                                {!! $hl($reg->name, $query) !!}
                                            </a>
                                        </h6>
                                        @if (count($path) > 1)
                                            <small class="text-muted d-block buscador-card-breadcrumb">{{ implode(' › ', $path) }}</small>
                                        @endif
                                    </div>
                                </div>

                                @if ($reg->description)
                                    <p class="buscador-detalle mb-3">{!! $hl(Str::limit($reg->description, 360), $query) !!}</p>
                                @endif

                                @php $regFiles = $reg->files ?? collect(); @endphp
                                <div class="d-flex flex-wrap gap-1 pt-1 border-top border-light mb-3">
                                    @if ($regFiles->where('tipo', 'pdf')->isNotEmpty())
                                        <span class="badge rounded-pill bg-danger text-white" style="font-size:.6rem;">
                                            <i class="fa fa-file-pdf me-1"></i>PDF
                                        </span>
                                    @endif
                                    @if ($regFiles->where('tipo', 'doc')->isNotEmpty())
                                        <span class="badge rounded-pill bg-secondary text-white" style="font-size:.6rem;">
                                            <i class="fa fa-file-word me-1"></i>Doc
                                        </span>
                                    @endif
                                    @if ($regFiles->where('tipo', 'audio')->isNotEmpty())
                                        <span class="badge rounded-pill bg-success text-white" style="font-size:.6rem;">
                                            <i class="fa fa-music me-1"></i>Audio
                                        </span>
                                    @endif
                                    @if ($regFiles->where('tipo', 'video')->isNotEmpty())
                                        <span class="badge rounded-pill bg-primary text-white" style="font-size:.6rem;">
                                            <i class="fa fa-video me-1"></i>Video
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ route('sisipedia.categories.show', $reg) }}" class="btn btn-sm buscador-btn-ver mt-auto">
                                    <i class="fa fa-arrow-right me-1"></i>Ver detalles
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- ── Videos institucionales ───────────────────────────────── --}}
            @if ($videosScored->isNotEmpty())
                <div class="col-12 mt-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge bg-danger text-white rounded-pill">{{ $videosCount }}</span>
                        <h3 class="h5 mb-0 fw-bold">Videos</h3>
                        <span class="badge rounded-pill bg-secondary text-white small fw-normal">Nombre o
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
                                <div class="px-3 pt-2 d-flex flex-wrap gap-2 border-bottom bg-light">
                                    <a href="https://drive.google.com/file/d/{{ $video->drive }}/view" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0">
                                        <i class="fa fa-external-link-alt me-1"></i>Drive
                                    </a>
                                    <a href="https://drive.google.com/uc?export=download&id={{ $video->drive }}" class="btn btn-sm btn-outline-secondary py-0" rel="nofollow">
                                        <i class="fa fa-download me-1"></i>Descargar
                                    </a>
                                </div>
                            @endif
                            <div class="card-body p-3 flex-grow-1 d-flex flex-column">
                                <h6 class="mb-2 text-dark fw-semibold text-break">{!! $hl($video->nombre, $query) !!}</h6>
                                @if ($video->descripcion)
                                    <p class="buscador-detalle mb-0">{!! $hl(Str::limit($video->descripcion, 320), $query) !!}</p>
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
                        <span class="badge bg-success text-white rounded-pill">{{ $librosCount }}</span>
                        <h3 class="h5 mb-0 fw-bold">Biblioteca</h3>
                        <span class="badge rounded-pill bg-secondary text-white small fw-normal">Textos — título o
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
                        <span class="badge rounded-pill bg-secondary text-white small fw-normal">Texto de la
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
                        <div class="card h-100 border-0 shadow-sm d-flex flex-column" style="border-left:4px solid {{ $color }} !important;">
                            <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0 text-white"
                                        style="width:40px;height:40px;min-width:40px;background:{{ $color }};">
                                        <i class="fa {{ $icon }}"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h6 class="mb-0 text-dark fw-semibold text-break">{!! $hl($ap->nombre_ol, $query) !!}</h6>
                                        <span class="badge rounded-pill text-white mt-1" style="background:{{ $color }};font-size:.65rem;">
                                            {{ \App\Models\sisipedia\Aportacion::etiquetaRol($ap->rol_nombre) }}
                                        </span>
                                    </div>
                                </div>
                                @if ($ap->category)
                                    <div class="mb-2">
                                        <a href="{{ route('sisipedia.categories.show', $ap->category) }}" class="btn btn-sm buscador-btn-ver w-100 mb-1">
                                            <i class="fa fa-arrow-right me-1"></i>Ver detalles
                                        </a>
                                        <small class="text-muted d-block buscador-card-breadcrumb" title="{{ implode(' › ', $catPath) }}">
                                            <i class="fa fa-sitemap me-1"></i>{{ implode(' › ', $catPath) }}
                                        </small>
                                    </div>
                                @endif
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @if ($ap->institucion)
                                        <span class="badge bg-secondary text-white" style="font-size:.65rem;">
                                            <i class="fa fa-building me-1"></i>{!! $hl($ap->institucion, $query) !!}
                                        </span>
                                    @endif
                                    @if ($ap->ubicacion)
                                        <span class="badge bg-secondary text-white" style="font-size:.65rem;">
                                            <i class="fa fa-map-marker me-1"></i>{!! $hl($ap->ubicacion, $query) !!}
                                        </span>
                                    @endif
                                </div>
                                @if ($ap->detalle)
                                    <p class="buscador-detalle mb-2">{!! $hl(Str::limit($ap->detalle, 320), $query) !!}</p>
                                @endif

                                @if ($ap->video)
                                    <div class="ratio ratio-16x9 mb-2 rounded overflow-hidden bg-dark border">
                                        <iframe loading="lazy" src="{{ \App\Services\GoogleDriveService::getPreviewUrl($ap->video) }}"
                                            class="border-0" allowfullscreen title="Video — {{ $ap->nombre_ol }}"></iframe>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <a href="{{ \App\Services\GoogleDriveService::getUrl($ap->video) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-external-link-alt me-1"></i>Abrir en Drive
                                        </a>
                                        <a href="https://drive.google.com/uc?export=download&id={{ $ap->video }}" class="btn btn-sm btn-outline-secondary" rel="nofollow">
                                            <i class="fa fa-download me-1"></i>Descargar
                                        </a>
                                    </div>
                                @endif

                                @if ($ap->audio)
                                    <div class="rounded overflow-hidden bg-light border mb-2">
                                        <iframe loading="lazy" src="{{ \App\Services\GoogleDriveService::getPreviewUrl($ap->audio) }}"
                                            class="w-100 border-0" style="height:120px;" title="Audio — {{ $ap->nombre_ol }}"></iframe>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <a href="{{ \App\Services\GoogleDriveService::getUrl($ap->audio) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                                            <i class="fa fa-external-link-alt me-1"></i>Abrir en Drive
                                        </a>
                                        <a href="https://drive.google.com/uc?export=download&id={{ $ap->audio }}" class="btn btn-sm btn-outline-secondary" rel="nofollow">
                                            <i class="fa fa-download me-1"></i>Descargar
                                        </a>
                                    </div>
                                @endif

                                @if ($ap->pdf || $ap->doc)
                                    <div class="d-flex flex-wrap gap-2 mt-auto pt-2 border-top border-light">
                                        @if ($ap->pdf)
                                            <a href="{{ \App\Services\GoogleDriveService::getUrl($ap->pdf) }}" target="_blank" rel="noopener" class="btn btn-sm btn-danger text-white">
                                                <i class="fa fa-file-pdf-o me-1"></i>Ver PDF
                                            </a>
                                            <a href="https://drive.google.com/uc?export=download&id={{ $ap->pdf }}" class="btn btn-sm btn-outline-danger" rel="nofollow">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        @endif
                                        @if ($ap->doc)
                                            <a href="{{ \App\Services\GoogleDriveService::getUrl($ap->doc) }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary text-white">
                                                <i class="fa fa-file-word-o me-1"></i>Ver Word
                                            </a>
                                            <a href="https://drive.google.com/uc?export=download&id={{ $ap->doc }}" class="btn btn-sm btn-outline-primary" rel="nofollow">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- ── Canciones ──────────────────────────────────────────────── --}}
            @if ($canciones->isNotEmpty())
                <div class="col-12 mt-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge rounded-pill bg-dark text-white">{{ $cancionesCount }}</span>
                        <h3 class="h5 mb-0 fw-bold">Canciones</h3>
                        <span class="badge rounded-pill bg-secondary text-white small fw-normal">Nombre o
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

        /* Resaltado de coincidencia */
        .buscador-hl {
            background: #fde68a;
            color: #1e293b;
            border-radius: 3px;
            padding: 0 2px;
            font-weight: 700;
            font-style: normal;
        }

        /* Botón Ver detalles */
        .buscador-btn-ver {
            background: linear-gradient(135deg, #1d4ed8 0%, #7c3aed 100%);
            color: #fff !important;
            border: none;
            font-weight: 500;
            letter-spacing: .01em;
            transition: filter .15s ease, transform .12s ease, box-shadow .15s ease;
            box-shadow: 0 2px 8px rgba(124, 58, 237, .25);
        }
        .buscador-btn-ver:hover {
            filter: brightness(1.12);
            transform: translateX(3px);
            box-shadow: 0 4px 14px rgba(124, 58, 237, .35);
            color: #fff !important;
        }

        /* Detalle legible en tarjetas */
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
