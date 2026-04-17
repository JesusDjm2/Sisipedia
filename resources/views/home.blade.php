@extends('layouts.app')
@section('titulo', 'Inicio')
@section('contenido')
    <div class="row">
        <div class="col-lg-12">
            @php
                $tituloDashboard = 'Administrador General Puklla Virtual'; // Título por defecto

                if (auth()->check()) {
                    $rol = auth()->user()->getRoleNames()->first(); // Obtener el primer rol del usuario

                    switch ($rol) {
                        case 'admin':
                            $tituloDashboard = 'Administrador General Puklla Virtual';
                            break;
                        case 'biblioteca':
                            $tituloDashboard = 'Administrador General Bibliotecario';
                            break;
                        case 'videos':
                            $tituloDashboard = 'Administrador General de Audios y Videos';
                            break;
                        case 'audios':
                            $tituloDashboard = 'Administrador General de Audios';
                            break;
                        case 'sisicha':
                            $tituloDashboard = 'Administrador General Sisipedia';
                            break;
                        case 'fredy':
                            $tituloDashboard = 'Administrador General Feria de Investigación';
                            break;
                        case 'alumno':
                            $tituloDashboard = 'Panel del Alumno - Puklla Virtual';
                            break;
                        default:
                            $tituloDashboard = 'Bienvenido a Puklla Virtual';
                    }
                }
            @endphp
            <h4 class="text-center mb-4 mt-3">{{ $tituloDashboard }}</h4>
        </div>

        @role('sisicha')
            <div class="col-lg-3 p-3">
                <a href="{{ route('sisipedia.categories.index') }}">
                    <img src="{{ asset('img/Sisichakuna.webp') }}" width="100%" loading="lazy">
                </a>
            </div>
            <div class="col-lg-9 p-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2">
                        <span class="font-weight-bold">
                            <i class="fa fa-layer-group text-primary mr-2"></i>Aportaciones recientes
                        </span>
                        <a href="{{ route('sisipedia.aportaciones.index') }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-th-list mr-1"></i>Ver todas
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $recientes = \App\Models\sisipedia\Aportacion::with('category')
                                ->latest()->limit(6)->get();
                            $rolColors = [
                                'Docente'         => 'primary',
                                'Líder'           => 'success',
                                'Niño/Estudiante' => 'warning',
                            ];
                            $rolIcons = [
                                'Docente'         => 'fa-graduation-cap',
                                'Líder'           => 'fa-star',
                                'Niño/Estudiante' => 'fa-child',
                            ];
                        @endphp
                        @if($recientes->isEmpty())
                            <div class="text-center py-4 text-muted small">
                                <i class="fa fa-inbox fa-lg mb-1 d-block"></i>
                                Aún no hay aportaciones registradas.
                            </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Rol</th>
                                        <th>Categoría</th>
                                        <th>Fecha</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recientes as $ap)
                                    <tr>
                                        <td class="align-middle small">{{ $ap->nombre_ol }}</td>
                                        <td class="align-middle">
                                            <span class="badge badge-{{ $rolColors[$ap->rol_nombre] ?? 'secondary' }}">
                                                <i class="fa {{ $rolIcons[$ap->rol_nombre] ?? 'fa-user' }} mr-1"></i>
                                                {{ $ap->rol_nombre }}
                                            </span>
                                        </td>
                                        <td class="align-middle small text-muted">
                                            {{ $ap->category->name ?? '—' }}
                                        </td>
                                        <td class="align-middle small text-muted text-nowrap">
                                            {{ $ap->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="align-middle text-right">
                                            @if($ap->category)
                                            <a href="{{ route('sisipedia.categories.admin-show', $ap->category) }}"
                                               class="btn btn-xs btn-outline-secondary">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endrole
        @role('admin')
            <div class="col-lg-3 p-3">
                <a href="{{ route('libros.index') }}">
                    <img src="{{ asset('img/Libros.webp') }}" width="100%" loading="lazy">
                </a>
            </div>
            <div class="col-lg-3 p-3">
                <a href="{{ route('libros.index') }}">
                    <img src="{{ asset('img/Sisichakuna.webp') }}" width="100%" loading="lazy">
                </a>
            </div>
            <div class="col-lg-3 p-3">
                <a href="{{ route('libros.index') }}">
                    <img src="{{ asset('img/Videos.webp') }}" width="100%" loading="lazy">
                </a>
            </div>
            <div class="col-lg-3 p-3">
                <a href="">
                    <img src="{{ asset('img/Canciones.webp') }}" width="100%" loading="lazy">
                </a>
            </div>
        @endrole
    </div>
@endsection
