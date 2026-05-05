@extends('layouts.padre')
@section('titulo', '- Página de Inicio')
@section('contenido')
    @if (session('busqueda_error'))
        <div class="container pt-5 mt-5">
            <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-3 border-0" role="alert">
                <i class="fas fa-search me-2"></i>{{ session('busqueda_error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    @endif
    <div class="fixed-image-container">
        <div class="overlay"></div>
        <div class="centered-content">
            <h2>SISIPEDIA</h2>
            <h1>Comunidad de interaprendizaje</h1>
        </div>
    </div>
    <div class="container mt-4 contenedorContenido">
        <div class="row cardIndex g-4 justify-content-center row-cols-1 row-cols-md-2 row-cols-xl-4">
            <div class="col">
                <div class="contenedor">
                    <img src="{{ asset('img/min/radio-Pukllasunchis.webp') }}" alt="" class="img-fluid">
                    <h2 class="position-absolute text-center text-white px-2">Sisichakunaq Pukllaynin</h2>
                    <a href="{{ route('public.sisi') }}">Ver registros</a>
                </div>
            </div>
            <div class="col">
                <div class="contenedor">
                    <img src="{{ asset('img/min/formacion-continua-pukllasunchis-01.webp') }}" alt="" class="img-fluid">
                    <h2 class="position-absolute text-center text-white">Videos</h2>
                    <a href="{{ route('videos') }}">Ver registros</a>
                </div>
            </div>
            <div class="col">
                <div class="contenedor">
                    <img src="{{ asset('img/min/libros-Pukllasunchis-min.webp') }}" alt="" class="img-fluid">
                    <h2 class="position-absolute text-center text-white px-2">Textos<br><small style="font-size: 12px;">(PDF / Word)</small></h2>
                    <a href="{{ route('libros') }}">Ver registros</a>
                </div>
            </div>
            <div class="col">
                <div class="contenedor contenedor-placeholder opacity-75">
                    <img src="{{ asset('img/min/libros-Pukllasunchis-min.webp') }}" alt="" class="img-fluid">
                    <h2 class="position-absolute text-center text-white">Fotos</h2>
                    <span class="position-absolute bottom-0 start-50 translate-middle-x mb-3 badge bg-secondary bg-opacity-75 rounded-pill">Próximamente</span>
                </div>
            </div>
        </div>

        {{-- @if ($sisipediaVideoFiles->isNotEmpty() || $sisipediaVideoAportaciones->isNotEmpty())
            <section id="home-videos-sisipedia" class="mt-5 pt-2">
                <div class="col-lg-12 mb-3">
                    <h2 class="titulosDos mb-1">Videos desde Sisipedia <span class="badge bg-light text-muted border fw-normal ms-1" style="font-size:.65rem;">Drive</span></h2>
                    <p class="text-muted small mb-0">Archivos de video subidos en registros públicos y en aportaciones.</p>
                </div>
                @if ($sisipediaVideoFiles->isNotEmpty())
                    <h3 class="h6 text-primary fw-bold mb-3"><i class="fa fa-folder-open me-2"></i>Registros de categorías</h3>
                    <div class="row g-4 mb-5">
                        @foreach ($sisipediaVideoFiles as $vFile)
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                                    <div class="ratio ratio-16x9 bg-dark">
                                        <iframe loading="lazy" class="border-0"
                                            src="{{ \App\Services\GoogleDriveService::getPreviewUrl($vFile->drive_id) }}"
                                            title="{{ $vFile->nombre_display }}" allowfullscreen></iframe>
                                    </div>
                                    <div class="card-body p-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary mb-2">Registro</span>
                                        <p class="small fw-semibold mb-1 text-dark">{{ $vFile->nombre_display }}</p>
                                        @if ($vFile->category)
                                            <a href="{{ route('sisipedia.categories.show', $vFile->category) }}" class="small text-decoration-none">
                                                <i class="fa fa-link me-1"></i>{{ $vFile->category->name }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if ($sisipediaVideoAportaciones->isNotEmpty())
                    <h3 class="h6 text-warning fw-bold mb-3"><i class="fa fa-users me-2"></i>Aportaciones</h3>
                    <div class="row g-4 mb-4">
                        @foreach ($sisipediaVideoAportaciones as $ap)
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-0 shadow-sm h-100 overflow-hidden border-start border-warning border-3">
                                    <div class="ratio ratio-16x9 bg-dark">
                                        <iframe loading="lazy" class="border-0"
                                            src="{{ \App\Services\GoogleDriveService::getPreviewUrl($ap->video) }}"
                                            title="{{ $ap->nombre_ol }}" allowfullscreen></iframe>
                                    </div>
                                    <div class="card-body p-3">
                                        <span class="badge bg-warning bg-opacity-25 text-dark border border-warning mb-2">Aportación</span>
                                        <p class="small fw-semibold mb-1 text-dark">{{ $ap->nombre_ol }}</p>
                                        @if ($ap->category)
                                            <a href="{{ route('sisipedia.categories.show', $ap->category) }}" class="small text-decoration-none">
                                                <i class="fa fa-link me-1"></i>{{ $ap->category->name }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if ($sisipediaAudioFiles->isNotEmpty() || $sisipediaAudioAportaciones->isNotEmpty())
            <section id="home-audios-sisipedia" class="mt-4 pt-3 border-top">
                <div class="col-lg-12 mb-3">
                    <h2 class="titulosDos mb-1">Audios desde Sisipedia</h2>
                    <p class="text-muted small mb-0">Archivos de audio en registros y en aportaciones (misma distinción que en videos).</p>
                </div>
                @if ($sisipediaAudioFiles->isNotEmpty())
                    <h3 class="h6 text-primary fw-bold mb-3"><i class="fa fa-folder-open me-2"></i>Registros de categorías</h3>
                    <div class="row g-4 mb-5">
                        @foreach ($sisipediaAudioFiles as $aFile)
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success mb-2">Registro · Audio</span>
                                        <iframe loading="lazy" class="w-100 rounded border-0 bg-light"
                                            style="height:120px;"
                                            src="{{ \App\Services\GoogleDriveService::getPreviewUrl($aFile->drive_id) }}"
                                            title="{{ $aFile->nombre_display }}" allowfullscreen></iframe>
                                        <p class="small fw-semibold mt-2 mb-1">{{ $aFile->nombre_display }}</p>
                                        @if ($aFile->category)
                                            <a href="{{ route('sisipedia.categories.show', $aFile->category) }}" class="small text-decoration-none">
                                                <i class="fa fa-link me-1"></i>{{ $aFile->category->name }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if ($sisipediaAudioAportaciones->isNotEmpty())
                    <h3 class="h6 text-warning fw-bold mb-3"><i class="fa fa-users me-2"></i>Aportaciones</h3>
                    <div class="row g-4 mb-4">
                        @foreach ($sisipediaAudioAportaciones as $ap)
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                                    <div class="card-body p-3">
                                        <span class="badge bg-warning bg-opacity-25 text-dark border border-warning mb-2">Aportación · Audio</span>
                                        <iframe loading="lazy" class="w-100 rounded border-0 bg-light"
                                            style="height:120px;"
                                            src="{{ \App\Services\GoogleDriveService::getPreviewUrl($ap->audio) }}"
                                            title="{{ $ap->nombre_ol }}" allowfullscreen></iframe>
                                        <p class="small fw-semibold mt-2 mb-1">{{ $ap->nombre_ol }}</p>
                                        @if ($ap->category)
                                            <a href="{{ route('sisipedia.categories.show', $ap->category) }}" class="small text-decoration-none">
                                                <i class="fa fa-link me-1"></i>{{ $ap->category->name }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif --}}

        @include('partials.home-aporte-form')

        <div class="row mb-4 mb-2 mt-5">
            <div class="col-lg-12">
                <h2 class="titulosDos">
                    Últimas publicaciones Sisichakunaq <a href="https://www.facebook.com/Sisichakunaq" class="verMas"
                        target="_blank">→ Ver en FaceBook <i class="fab fa-facebook" style="font-size: 15px"></i></a>
                </h2>
            </div>
            <div class="col-lg-6">
                <iframe width="100%" height="400" src="https://www.youtube.com/embed/NtcKGRUgnrs"
                    title="Pukllasunchis | Sisichakunaq Pukllaynin | Radio para niñas y niños andinos" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="col-lg-6">
                <iframe width="100%" height="400" src="https://www.youtube.com/embed/7wdm2XdChZI"
                    title="Pukllasunchis | Kawsay" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-2">
                <h2 class="titulosDos">Últimas publicaciones Sisichakunaq <a target="_blank"
                        href="https://open.spotify.com/show/21uqGdVqtet2iYx3XkhAVh" class="verMas">→ Ver en Spotify <i
                            class="fab fa-spotify" style="font-size: 15px"></i> </a></h2>
            </div>
            <div class="col-lg-3 p-3">
                <iframe style="border-radius:12px"
                    src="https://open.spotify.com/embed/episode/2jdkuFtfvjoEDqPryvBMAK?utm_source=generator&theme=0"
                    width="100%" height="352" frameBorder="0" allowfullscreen=""
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                    loading="lazy"></iframe>
            </div>
            <div class="col-lg-3 p-3">
                <iframe style="border-radius:12px"
                    src="https://open.spotify.com/embed/episode/59RAn2Ev1KkZy0dUBTJfKw?utm_source=generator" width="100%"
                    height="352" frameBorder="0" allowfullscreen=""
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                    loading="lazy"></iframe>
            </div>
            <div class="col-lg-3 p-3">
                <iframe style="border-radius:12px"
                    src="https://open.spotify.com/embed/episode/5IJQXlHveuE3iAP0dNvRoW?utm_source=generator&theme=0"
                    width="100%" height="352" frameBorder="0" allowfullscreen=""
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                    loading="lazy"></iframe>
            </div>
            <div class="col-lg-3 p-3">
                <iframe style="border-radius:12px"
                    src="https://open.spotify.com/embed/episode/6qdHD2sbWtUcDtpzZWh6Yb?utm_source=generator"
                    width="100%" height="352" frameBorder="0" allowfullscreen=""
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                    loading="lazy"></iframe>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-lg-12">
                <h2 class="mt-3 mb-4 titulosDos">Últimas publicaciones de textos <a href="{{ route('libros') }}"
                        class="verMas">→ Ver todos los textos</a></h2>
            </div>
            <div class="normalSize">
                <div class="row">
                    @foreach ($libros as $libro)
                        <div class="col-lg-3 mb-4 cardLibros" data-nombre="{{ $libro->nombre }}"
                            data-autor="{{ $libro->autor }}">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class='text-center'>{{ $libro->nombre }}</h5>
                                    <p class="text-center"><i style="font-size: 12px" class="fa fa-user"></i>
                                        {{ $libro->autor }}
                                    </p>
                                    @php
                                        $idDocumento = $libro->identificador;
                                        $urlVistaPrevia = "https://drive.google.com/thumbnail?id={$idDocumento}&sz=w1000";
                                    @endphp
                                    <div class="contenedorLibro">
                                        <div class="contenedorImagen">
                                            <img class="vistaPrevia" src="{{ $urlVistaPrevia }}"
                                                alt="Vista previa del PDF">
                                            <div class="overlay">
                                                <a href="https://drive.google.com/file/d/{{ $libro->identificador }}/view"
                                                    target="_blank">
                                                    <button class="btnAbrirPDF">Abrir PDF</button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="responsive">
                <div class="row">
                    @foreach ($libros->take(5) as $libro)
                        <div class="col-lg-3 mb-4 cardLibros p-3" data-nombre="{{ $libro->nombre }}"
                            data-autor="{{ $libro->autor }}">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class='text-center'>{{ $libro->nombre }}</h5>
                                    <p class="text-center"><i style="font-size: 12px" class="fa fa-user"></i>
                                        {{ $libro->autor }}
                                    </p>
                                    @php
                                        $idDocumento = $libro->identificador;
                                        $urlVistaPrevia = "https://drive.google.com/thumbnail?id={$idDocumento}&sz=w1000";
                                    @endphp
                                    <div class="contenedorLibro">
                                        <div class="contenedorImagen">
                                            <img class="vistaPrevia" src="{{ $urlVistaPrevia }}"
                                                alt="Vista previa del PDF">
                                            <div class="overlay">
                                                <a href="https://drive.google.com/file/d/{{ $libro->identificador }}/preview"
                                                    target="_blank">
                                                    <button class="btnAbrirPDF">Abrir PDF</button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="normalSize">
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h2 class="titulosDos">
                        Últimos videos
                    </h2>
                </div>
                <!------Video en Drive--------->
                @foreach ($videos as $video)
                    <div class="col-lg-4 col-md-6 p-3">
                        <div class="cardVideos">
                            <div class="ratio ratio-16x9">
                                @if ($video->youtube)
                                    <iframe loading="lazy" src="https://www.youtube.com/embed/{{ $video->youtube }}"
                                        title="YouTube video" allowfullscreen frameborder="0"></iframe>
                                @elseif ($video->drive)
                                    <iframe loading="lazy"
                                        src="https://drive.google.com/file/d/{{ $video->drive }}/preview" allowfullscreen
                                        frameborder="0"></iframe>
                                @endif
                            </div>
                            <h6>{{ $video->nombre }}
                                <small class="float-end">
                                    <a href="{{ route('videoscat.show', $video->categoria->url) }}"
                                        style="text-decoration: none; color:rgb(79 172 255) !important">
                                        {{ $video->categoria->nombre }} <i class="fa fa-link fa-sm"></i>
                                    </a>
                                </small>
                            </h6>
                            <p>{{ $video->descripcion }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="responsive">
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h2 class="titulosDos">
                        Últimos videos
                    </h2>
                </div>
                <!------Video en Drive--------->
                @foreach ($videos->take(4) as $video)
                    <div class="col-lg-4 p-3">
                        @if ($video->youtube)
                            <h4>{{ $video->nombre }}</h4>
                            <div class="video-thumbnail" style="position: relative; width: 100%; height: 261px;">
                                <img loading="lazy" src="https://img.youtube.com/vi/{{ $video->youtube }}/hqdefault.jpg"
                                    alt="Video Thumbnail" style="width: 100%; height: 100%;">
                                <a href="https://www.youtube.com/watch?v={{ $video->youtube }}" target="_blank"
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                    <i class="fa-brands fa-youtube" style="color: red; font-size:60px"></i>
                                </a>
                            </div>
                        @elseif($video->drive)
                            <h4>{{ $video->nombre }}</h4>
                            <iframe width="100%" height="261" loading="lazy"
                                src="https://drive.google.com/file/d/{{ $video->drive }}/preview"
                                class="embed-responsive-item" allowfullscreen></iframe>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        {{-- <div class="container mt-4">
            <div class="row">
                <div class="col-lg-4">
                    <h2 style="font-size:20px" class="mt-3 mb-4 titulosDos">Publicaciones EESP Pukllasunchis<a
                            href="https://www.facebook.com/eesp.pukllasunchis" target="_blank" class="verMas">→ Ver Fan
                            page </a></h2>
                    <div class="facebook" style="width: 100%;">
                        <div class="fb-page" data-href="https://www.facebook.com/eesp.pukllasunchis" data-tabs="timeline"
                            data-width="" data-height="" data-small-header="false" data-adapt-container-width="true"
                            data-hide-cover="false" data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/eesp.pukllasunchis" class="fb-xfbml-parse-ignore">
                                <a href="https://www.facebook.com/eesp.pukllasunchis">EESP Pukllasunchis</a>
                            </blockquote>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h2 style="font-size:20px" class="mt-3 mb-4 titulosDos">Publicaciones Colegio Pukllasunchis<a
                            href="https://www.facebook.com/colegio.pukllasunchis" target="_blank" class="verMas">→ Ver
                            Fan page </a></h2>
                    <div class="facebook" style="width: 100%;">
                        <div class="fb-page" data-href="https://www.facebook.com/colegio.pukllasunchis"
                            data-tabs="timeline" data-width="" data-height="" data-small-header="false"
                            data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/colegio.pukllasunchis"
                                class="fb-xfbml-parse-ignore"><a
                                    href="https://www.facebook.com/colegio.pukllasunchis">Colegio Pukllasunchis</a>
                            </blockquote>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h2 style="font-size:20px" class="mt-3 mb-4 titulosDos">Publicaciones Centro Cultural<a
                            href="https://www.facebook.com/CentroCulturalPukllasunchis" target="_blank" class="verMas">→
                            Ver
                            Fan page </a></h2>
                    <div class="facebook" style="width: 100%;">
                        <div class="fb-page" data-href="https://www.facebook.com/CentroCulturalPukllasunchis"
                            data-tabs="timeline" data-width="" data-height="" data-small-header="false"
                            data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/CentroCulturalPukllasunchis"
                                class="fb-xfbml-parse-ignore"><a
                                    href="https://www.facebook.com/CentroCulturalPukllasunchis">Centro Cultural
                                    Pukllasunchis</a></blockquote>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
@endsection
