@php
    $isSisipedia = request()->routeIs('public.sisi', 'public.categoria.sisi', 'sisipedia.categories.show', 'sisipedia.search');
    $isVideos = request()->routeIs('videos');
    $rn = request()->route()?->getName();
    $isLibros = $rn === 'libros' || (is_string($rn) && str_starts_with($rn, 'libros.'));
@endphp
<div>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top menuPuklla" role="navigation"
        aria-label="Principal">
        <div class="container py-2 py-lg-1">
            <a class="navbar-brand mb-0" href="{{ route('index') }}">
                <img src="{{ asset('img/Asociacion-logo-rojo.png') }}" alt="Logo Asociación Puklla"
                    class="logo d-block" width="150" height="auto" loading="lazy">
            </a>
            <button class="navbar-toggler border-secondary-subtle shadow-sm" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Abrir menú principal">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between align-items-lg-center gap-lg-4"
                id="navbarNav">
                <ul
                    class="navbar-nav menu-main-nav mb-3 mb-lg-0 mx-lg-auto py-lg-2 gap-lg-3 gap-xl-4 align-items-lg-center flex-column flex-lg-row">
                    <li class="nav-item">
                        <a class="nav-link px-lg-3 {{ $isSisipedia ? 'active' : '' }}" @if ($isSisipedia) aria-current="page" @endif
                            href="{{ route('public.sisi') }}">Sisipedia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-lg-3 {{ $isVideos ? 'active' : '' }}" @if ($isVideos) aria-current="page" @endif
                            href="{{ route('videos') }}">Videos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-lg-3 {{ $isLibros ? 'active' : '' }}" @if ($isLibros) aria-current="page" @endif
                            href="{{ route('libros') }}">Biblioteca</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link menu-nav-placeholder px-lg-3 mb-0">Fotos</span>
                    </li>
                </ul>

                <div class="menu-toolbar-wrap d-flex flex-column align-items-center align-items-lg-end">
                    <ul
                        class="navbar-nav menu-toolbar mb-0 align-items-center justify-content-center gap-2 gap-sm-3 flex-row flex-wrap">
                        <li class="nav-item">
                            <a class="nav-link icon text-secondary" href="#" aria-label="Facebook"><i
                                    class="fab fa-facebook-f"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link icon text-secondary" href="#" aria-label="Twitter"><i
                                    class="fab fa-twitter"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link icon text-secondary" href="#" aria-label="Instagram"><i
                                    class="fab fa-instagram"></i></a>
                        </li>
                        <li class="nav-item">
                            @if (Auth::check() && Auth::user()->hasRole('alumno'))
                                <div class="dropdown">
                                    <button id="navbarUserMenu" type="button"
                                        class="btn btn-link nav-link icon text-secondary text-decoration-none p-0 border-0"
                                        data-bs-toggle="dropdown" aria-expanded="false"
                                        aria-label="Menú de usuario">
                                        <i class="fas fa-user"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                                        aria-labelledby="navbarUserMenu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('logout') }}"
                                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                {{ __('Logout') }}
                                            </a>
                                        </li>
                                    </ul>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            @elseif (Auth::check() && Auth::user()->hasRole('biblioteca'))
                                <a class="nav-link icon text-secondary" href="{{ route('bibliotecario') }}"
                                    aria-label="Ir a panel bibliotecario"><i class="fas fa-user"></i></a>
                            @elseif (Auth::check() && Auth::user()->hasRole('videos'))
                                <a class="nav-link icon text-secondary" href="{{ route('adminVideos') }}"
                                    aria-label="Ir a administración de videos"><i class="fas fa-user"></i></a>
                            @elseif (Auth::check() && Auth::user()->hasRole('audios'))
                                <a class="nav-link icon text-secondary" href="{{ route('adminCanciones') }}"
                                    aria-label="Ir a administración de canciones"><i class="fas fa-user"></i></a>
                            @elseif (Auth::check() && Auth::user()->hasRole('sisicha'))
                                <a class="nav-link icon text-secondary" href="{{ route('adminSisicha') }}"
                                    aria-label="Ir a Sisicha"><i class="fas fa-user"></i></a>
                            @else
                                <a class="nav-link icon text-secondary" href="{{ route('home') }}"
                                    aria-label="Ingresar"><i class="fas fa-user"></i></a>
                            @endif
                        </li>
                        <li class="nav-item">
                            <button type="button"
                                class="btn btn-link nav-link icon text-secondary text-decoration-none p-0 border-0"
                                data-bs-toggle="modal" data-bs-target="#menuSearchModal"
                                aria-label="Abrir buscador">
                                <i class="fas fa-search"></i>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    {{-- Buscador en modal centrado (no altera el layout del menú) --}}
    <div class="modal fade menu-search-modal" id="menuSearchModal" tabindex="-1"
        aria-labelledby="menuSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="menu-search-modal-accent" aria-hidden="true"></div>
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div>
                        <h2 class="modal-title h5 fw-bold mb-1 font-serif-menu" id="menuSearchModalLabel">
                            Buscar en el sitio
                        </h2>
                        <p class="small text-muted mb-0 pe-3">
                            Registros Sisipedia, <strong>nombres de archivos</strong> adjuntos, videos institucionales,
                            biblioteca y más.
                        </p>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-2">
                    <form method="GET" action="{{ route('busqueda') }}" id="menuSearchForm">
                        
                        <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border border-light">
                            <span class="input-group-text bg-white border-end-0 text-primary"><i
                                    class="fas fa-search"></i></span>
                            <input id="menuSearchInput" type="search" name="query"
                                class="form-control border-start-0 js-menu-search-input"
                                placeholder="Ej. planta medicinal, taller 2024…" minlength="3" maxlength="120"
                                autocomplete="off" required aria-describedby="menuSearchHint">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold menu-search-submit">
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function onReady(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    onReady(function () {
        var modalEl = document.getElementById('menuSearchModal');
        var input = document.querySelector('.js-menu-search-input');
        if (modalEl && input) {
            modalEl.addEventListener('shown.bs.modal', function () {
                input.focus();
                input.select();
            });
            modalEl.addEventListener('hidden.bs.modal', function () {
                var form = document.getElementById('menuSearchForm');
                if (form) form.reset();
            });
        }
    });
})();
</script>
