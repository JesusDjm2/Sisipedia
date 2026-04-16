@extends('layouts.app')
@section('titulo', 'Detalle de registro')
@section('contenido')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h3 class="mb-1">
                    <i class="fa fa-folder-open me-2"></i>Detalle del registro
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('sisipedia.categories.index') }}" class="text-decoration-none">Sisipedia</a>
                        </li>
                        @foreach ($breadcrumbs as $item)
                            @if ($loop->last)
                                <li class="breadcrumb-item active" aria-current="page">{{ $item->name }}</li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="{{ route('sisipedia.categories.admin-show', $item) }}"
                                        class="text-decoration-none">{{ $item->name }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('sisipedia.categories.edit', $category) }}" class="btn btn-warning btn-sm">
                    <i class="fa fa-edit me-1"></i>Editar
                </a>
                <a href="{{ route('sisipedia.categories.show', $category) }}" class="btn btn-info btn-sm" target="_blank">
                    <i class="fa fa-external-link-alt me-1"></i>Ver público
                </a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row gap-3">
                            <div class="flex-shrink-0">
                                @if ($category->image)
                                    <img src="{{ asset($category->image) }}" alt="{{ $category->name }}"
                                        class="rounded border" width="120" height="120" style="object-fit: cover;">
                                @else
                                    <div class="rounded border bg-light d-flex align-items-center justify-content-center"
                                        style="width: 120px; height: 120px;">
                                        <i class="fa fa-image text-muted fa-2x"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary">{{ $category->numbering }}</span>
                                    <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $category->is_active ? 'Público (activo)' : 'No público (inactivo)' }}
                                    </span>
                                    @if ($category->parent)
                                        <span class="badge bg-light text-dark border">
                                            Padre: {{ $category->parent->name }}
                                        </span>
                                    @endif
                                </div>

                                <h4 class="mb-1">{{ $category->name }}</h4>
                                <div class="text-muted small mb-2">
                                    <i class="fa fa-tag me-1"></i>{{ $category->slug ?: 'sin-slug' }}
                                    <span class="mx-2">•</span>
                                    <i class="fa fa-sort-numeric-down me-1"></i>Orden: {{ $category->order }}
                                </div>

                                <p class="mb-0">
                                    {{ $category->description ?: 'Sin descripción.' }}
                                </p>
                            </div>
                        </div>

                        {{-- Archivos multimedia --}}
                        @if($category->pdf || $category->audio || $category->video)
                            <hr>
                            <div class="row g-3 mt-1">

                                @if($category->pdf)
                                    <div class="col-12">
                                        <h6 class="text-muted mb-2"><i class="fa fa-file-pdf text-danger me-1"></i> PDF</h6>
                                        <iframe src="{{ \App\Services\GoogleDriveService::getPreviewUrl($category->pdf) }}"
                                                width="100%" height="480" allow="autoplay"
                                                class="rounded border"></iframe>
                                        <div class="mt-1">
                                            <a href="{{ \App\Services\GoogleDriveService::getEmbedUrl($category->pdf) }}"
                                               class="btn btn-sm btn-outline-danger" target="_blank">
                                                <i class="fa fa-download me-1"></i> Descargar PDF
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if($category->audio)
                                    <div class="col-12">
                                        <h6 class="text-muted mb-2"><i class="fa fa-music text-primary me-1"></i> Audio</h6>
                                        <audio controls class="w-100" style="max-width: 500px;">
                                            <source src="{{ \App\Services\GoogleDriveService::getEmbedUrl($category->audio) }}">
                                            Tu navegador no soporta el reproductor de audio.
                                        </audio>
                                        <div class="mt-1">
                                            <a href="{{ \App\Services\GoogleDriveService::getUrl($category->audio) }}"
                                               class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fa fa-external-link-alt me-1"></i> Abrir en Drive
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if($category->video)
                                    <div class="col-12">
                                        <h6 class="text-muted mb-2"><i class="fa fa-video text-success me-1"></i> Video</h6>
                                        <div class="ratio ratio-16x9" style="max-width: 720px;">
                                            <iframe src="{{ \App\Services\GoogleDriveService::getPreviewUrl($category->video) }}"
                                                    allow="autoplay" allowfullscreen
                                                    class="rounded border"></iframe>
                                        </div>
                                        <div class="mt-1">
                                            <a href="{{ \App\Services\GoogleDriveService::getUrl($category->video) }}"
                                               class="btn btn-sm btn-outline-success" target="_blank">
                                                <i class="fa fa-external-link-alt me-1"></i> Abrir en Drive
                                            </a>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <strong><i class="fa fa-sitemap me-2"></i>Hijos</strong>
                    </div>
                    <div class="card-body">
                        @if ($category->children->count())
                            <div class="list-group">
                                @foreach ($category->children as $child)
                                    <a href="{{ route('sisipedia.categories.admin-show', $child) }}"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span class="text-truncate">
                                            <span class="badge bg-light text-dark border me-2">{{ $child->numbering }}</span>
                                            {{ $child->name }}
                                        </span>
                                        <span class="badge {{ $child->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $child->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">
                                <i class="fa fa-info-circle me-1"></i>No tiene hijos.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
