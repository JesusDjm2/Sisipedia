@extends('layouts.padre')
@section('titulo', '- Detalle de registro')
@section('contenido')
    <div class="fixed-image-container">
        <div class="overlay"></div>
        <div class="centered-content">
            <h2>SISIPEDIA</h2>
            <h1>{{ $category->name ?? 'Detalle del registro' }}</h1>
        </div>
    </div>
    <div class="container">
        <div class="row mt-2 mt-md-5">
            <div class="col-lg-12">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb bg-white shadow-sm rounded-3 px-3 py-2 mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('public.sisi') }}" class="text-decoration-none">
                                <i class="fa fa-home me-1"></i>Registros
                            </a>
                        </li>
                        @foreach ($breadcrumbs as $item)
                            @if ($loop->last)
                                <li class="breadcrumb-item active" aria-current="page">{{ $item->name }}</li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="{{ route('sisipedia.categories.show', $item) }}"
                                        class="text-decoration-none">{{ $item->name }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                            <div class="card-body p-3 p-md-4">
                                <div class="rounded-3 border mb-3 overflow-hidden bg-light">
                                    @if ($category->image)
                                        <img src="{{ asset($category->image) }}" alt="{{ $category->name }}"
                                            class="img-fluid w-100" style="max-height: 320px; object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center" style="min-height: 180px;">
                                            <i class="fa fa-folder-open fa-3x text-primary"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary">Registro {{ $category->numbering }}</span>
                                    @if ($category->parent)
                                        <span class="badge bg-light text-dark border">Hereda de:
                                            {{ $category->parent->name }}</span>
                                    @endif
                                </div>

                                <h1 class="h3 mb-2">{{ $category->name }}</h1>
                                <p class="text-muted mb-3">
                                    {{ $category->description ?: 'Este registro no tiene descripción adicional.' }}
                                </p>

                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge rounded-pill bg-light text-dark border">
                                        <i class="fa fa-tag me-1"></i>{{ $category->slug ?: 'sin-slug' }}
                                    </span>
                                    <span class="badge rounded-pill bg-light text-dark border">
                                        <i class="fa fa-sort-numeric-down me-1"></i>Orden: {{ $category->order }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-3 px-md-4">
                                <h2 class="h5 mb-0">
                                    <i class="fa fa-sitemap me-2 text-primary"></i>Registros heredados
                                </h2>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                @php
                                    $publicChildren = $category->children->where('is_active', true);
                                @endphp
                                @if ($publicChildren->count())
                                    <div class="d-grid gap-3">
                                        @foreach ($publicChildren as $child)
                                            <a href="{{ route('sisipedia.categories.show', $child) }}"
                                                class="card border text-decoration-none">
                                                <div class="d-flex gap-3 align-items-start">
                                                    @if ($child->image)
                                                        <img src="{{ asset($child->image) }}" alt="{{ $child->name }}"
                                                            class="rounded flex-shrink-0 m-2" width="52" height="52"
                                                            style="object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0 m-2"
                                                            style="width: 40px; height: 40px;">
                                                            <i class="fa fa-bookmark"></i>
                                                        </div>
                                                    @endif
                                                    <div class="p-2 ps-0">
                                                        <div class="fw-semibold text-dark">{{ $child->name }}</div>
                                                        <small class="text-muted d-block">Nivel
                                                            {{ $child->numbering }}</small>
                                                        @if ($child->description)
                                                            <small
                                                                class="text-muted d-block mt-1">{{ Str::limit($child->description, 80) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-light border mb-0">
                                        <i class="fa fa-info-circle me-2"></i>No existen registros hijos para este nivel.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Recursos multimedia ──────────────────────────────────── --}}
                @if ($category->pdf || $category->audio || $category->video)
                    <div class="row g-4 mt-1">

                        {{-- PDF --}}
                        @if ($category->pdf)
                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                    <div class="card-header bg-white border-0 pt-3 pb-0 px-3 px-md-4 d-flex align-items-center justify-content-between">
                                        <h2 class="h5 mb-0">
                                            <i class="fa fa-file-pdf text-danger me-2"></i>Documento PDF
                                        </h2>
                                        <a href="{{ \App\Services\GoogleDriveService::getUrl($category->pdf) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-external-link-alt me-1"></i>Abrir en Drive
                                        </a>
                                    </div>
                                    <div class="card-body p-0">
                                        <iframe
                                            src="{{ \App\Services\GoogleDriveService::getPreviewUrl($category->pdf) }}"
                                            class="w-100 border-0"
                                            style="height: 520px;"
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Audio --}}
                        @if ($category->audio)
                            <div class="col-12 col-md-6">
                                <div class="card border-0 shadow-sm rounded-4 h-100">
                                    <div class="card-header bg-white border-0 pt-3 pb-0 px-3 px-md-4 d-flex align-items-center justify-content-between">
                                        <h2 class="h5 mb-0">
                                            <i class="fa fa-music text-success me-2"></i>Audio
                                        </h2>
                                        <a href="{{ \App\Services\GoogleDriveService::getUrl($category->audio) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fa fa-external-link-alt me-1"></i>Abrir en Drive
                                        </a>
                                    </div>
                                    <div class="card-body p-3 p-md-4">
                                        <iframe
                                            src="{{ \App\Services\GoogleDriveService::getPreviewUrl($category->audio) }}"
                                            class="w-100 border-0 rounded-3"
                                            style="height: 180px;"
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Video --}}
                        @if ($category->video)
                            <div class="col-12 @if($category->audio) col-md-6 @endif">
                                <div class="card border-0 shadow-sm rounded-4 h-100">
                                    <div class="card-header bg-white border-0 pt-3 pb-0 px-3 px-md-4 d-flex align-items-center justify-content-between">
                                        <h2 class="h5 mb-0">
                                            <i class="fa fa-video text-primary me-2"></i>Video
                                        </h2>
                                        <a href="{{ \App\Services\GoogleDriveService::getUrl($category->video) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-external-link-alt me-1"></i>Abrir en Drive
                                        </a>
                                    </div>
                                    <div class="card-body p-0">
                                        <iframe
                                            src="{{ \App\Services\GoogleDriveService::getPreviewUrl($category->video) }}"
                                            class="w-100 border-0 rounded-bottom-4"
                                            style="height: 320px;"
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
