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
                <a href="{{ route('libros.index') }}">
                    <img src="{{ asset('img/Sisichakuna.webp') }}" width="100%" loading="lazy">
                </a>
            </div>
            <div class="col-lg-3 p3">
                <a href="{{ route('sisipedia.categories.index') }}">IR</a>
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
