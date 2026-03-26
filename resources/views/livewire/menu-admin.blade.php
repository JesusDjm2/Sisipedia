<nav id="sidebar">
    <div class="custom-menu">
        <button type="button" id="sidebarCollapse" class="btn btn-primary">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle Menu</span>
        </button>
    </div>
    <div class="p-4 pt-5">
        <h1><a href="{{ route('home') }}" class="logo"><img src="{{ asset('img/Asociacion-logo-blanco.png') }}"
                    width="100%" alt=""></a></h1>
        <ul class="list-unstyled components mb-5">
            @role('admin')
                <li class="active">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-book"></i> Biblioteca</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        <li>
                            <a href="{{ route('libros.index') }}"><span class="flecha-admin">→</span> Libros</a>
                        </li>
                        <li>
                            <a href="{{ route('categorias.index') }}"><span class="flecha-admin">→</span> Categorias</a>
                        </li>
                        <li>
                            <a href="{{ route('secciones.index') }}"><span class="flecha-admin">→</span> Secciones</a>
                        </li>


                    </ul>
                </li>
                <li>
                    <a href="#canciones" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-music"></i> Canciones</a>
                    <ul class="collapse list-unstyled" id="canciones">
                        <li>
                            <a href="{{ route('canciones.index') }}"><span class="flecha-admin">→</span> Canciones</a>
                        </li>
                        <li>
                            <a href="{{ route('cancionescat.index') }}"><span class="flecha-admin">→</span> Categorias</a>
                        </li>
                    </ul>
                </li>
                <li>
                <li>
                    <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-music"></i> Sisichakunay</a>
                    <ul class="collapse list-unstyled" id="pageSubmenu">
                        <li>
                            <a href="#">Page 1</a>
                        </li>
                        <li>
                            <a href="#">Page 2</a>
                        </li>
                        <li>
                            <a href="#">Page 3</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#videos" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-video-camera"></i> Videos</a>
                    <ul class="collapse list-unstyled" id="videos">
                        <li>
                            <a href="{{ route('videos.index') }}"><span class="flecha-admin">→</span> Videos</a>
                        </li>
                        <li>
                            <a href="{{ route('videoscat.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                    </ul>
                </li>
            @endrole
            {{-- @role('sisicha')
                Sisichas!
            @endrole --}}
            @role('biblioteca')
                <li class="active">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-book"></i> Biblioteca</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        <li>
                            <a href="{{ route('libros.index') }}"><span class="flecha-admin">→</span> Libros</a>
                        </li>
                        <li>
                            <a href="{{ route('categorias.index') }}"><span class="flecha-admin">→</span> Categorias</a>
                        </li>
                        <li>
                            <a href="{{ route('secciones.index') }}"><span class="flecha-admin">→</span> Secciones</a>
                        </li>
                    </ul>
                </li>
            @endrole
            @role('videos')
                <li>
                    <a href="#videos" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-video-camera"></i> Videos</a>
                    <ul class="collapse list-unstyled" id="videos">
                        <li>
                            <a href="{{ route('videos.index') }}"><span class="flecha-admin">→</span> Videos</a>
                        </li>
                        <li>
                            <a href="{{ route('videoscat.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                    </ul>
                </li>
            @endrole
            @role('audios')
                <li>
                    <a href="#canciones" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-music"></i> Canciones</a>
                    <ul class="collapse list-unstyled" id="canciones">
                        <li>
                            <a href="{{ route('canciones.index') }}"><span class="flecha-admin">→</span> Canciones</a>
                        </li>
                        <li>
                            <a href="{{ route('cancionescat.index') }}"><span class="flecha-admin">→</span>
                                Categorias</a>
                        </li>

                    </ul>
                </li>
            @endrole
            <li>
                <a href="#admins" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                        class="fa fa-building"></i> Usuarios</a>
                <ul class="collapse list-unstyled" id="admins">
                    <li>
                        <a href="{{ route('admin.index') }}"><span class="flecha-admin">→</span> Administradores</a>
                    </li>
                    <li>
                        <a href="{{ route('alumnos') }}"><span class="flecha-admin">→</span> Alumnos</a>
                    </li>
                </ul>
            </li>
            <style>
                .flecha-admin {
                    opacity: 0;
                    transform: translateX(-20px);
                    transition: opacity 0.5s ease, transform 0.5s ease;
                    display: inline-block;
                    /* Asegúrate de que el span sea un elemento en línea en bloque */
                }

                /* Al hacer hover sobre el enlace, el span aparece con un fade in y se traslada a su posición original */
                a:hover .flecha-admin {
                    opacity: 1;
                    transform: translateX(0);
                }
            </style>
            <li>
                <a href="{{ route('index') }}"><i class="fa fa-home"></i> Volver al inicio</a>
            </li>
        </ul>
        <div class="mb-5">
            <a class="nav-link text-white text-center" href="{{ route('logout') }}"
                onclick="event.preventDefault();
                  document.getElementById('logout-form').submit();">
                {{ Auth::user()->name }} - {{ __('Logout') }} <i class="fa fa-sign-out"></i>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
        <div class="footer">
            <p class="text-center">
                Copyright
                &copy;
                <script>
                    document.write(new Date().getFullYear());
                </script> <br> Made by <a href="" target="_blank">DJM2 </a>
            </p>
        </div>
    </div>
</nav>
