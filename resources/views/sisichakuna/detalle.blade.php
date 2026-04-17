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

                {{-- Layout principal: contenido izquierda + sidebar sticky derecha --}}
                <div class="row g-4 align-items-start">

                    {{-- ── Columna principal (info + multimedia) ────────────── --}}
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                            {{-- Imagen --}}
                            @if ($category->image)
                                <img src="{{ asset($category->image) }}" alt="{{ $category->name }}"
                                    class="img-fluid w-100" style="max-height: 340px; object-fit: cover;">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light"
                                     style="min-height: 160px;">
                                    <i class="fa fa-folder-open fa-3x text-primary opacity-50"></i>
                                </div>
                            @endif

                            <div class="card-body p-3 p-md-4">
                                {{-- Breadcrumb de herencia (sin numeración) --}}
                                @if ($category->parent)
                                    <p class="text-muted small mb-2">
                                        <i class="fa fa-level-up-alt me-1 fa-rotate-90"></i>Pertenece a:
                                        <a href="{{ route('sisipedia.categories.show', $category->parent) }}"
                                           class="text-decoration-none fw-semibold">{{ $category->parent->name }}</a>
                                    </p>
                                @endif

                                <h1 class="h3 fw-bold mb-2">{{ $category->name }}</h1>
                                <p class="text-muted mb-0">
                                    {{ $category->description ?: 'Este registro no tiene descripción adicional.' }}
                                </p>
                            </div>

                            {{-- ── Archivos adjuntos: grid 3 columnas, por formato ──────── --}}
                            @if ($category->files->isNotEmpty())
                                <div class="border-top px-3 px-md-4 py-3 py-md-4">
                                    <h2 class="h6 fw-bold mb-3 text-secondary">
                                        <i class="fa fa-paperclip me-2 text-primary"></i>Archivos adjuntos
                                    </h2>
                                    <div class="row g-3">
                                        @foreach ($category->files as $archivo)
                                            @php
                                                $tipoMeta = [
                                                    'pdf' => [
                                                        'label' => 'PDF',
                                                        'color' => 'danger',
                                                        'icon' => 'fa-file-pdf',
                                                        'preview' => 'iframe',
                                                        'iframe_h' => 340,
                                                    ],
                                                    'doc' => [
                                                        'label' => 'Word',
                                                        'color' => 'primary',
                                                        'icon' => 'fa-file-word',
                                                        'preview' => 'none',
                                                        'iframe_h' => null,
                                                    ],
                                                    'audio' => [
                                                        'label' => 'Audio',
                                                        'color' => 'success',
                                                        'icon' => 'fa-music',
                                                        'preview' => 'iframe',
                                                        'iframe_h' => 120,
                                                    ],
                                                    'video' => [
                                                        'label' => 'Video',
                                                        'color' => 'warning',
                                                        'icon' => 'fa-video',
                                                        'preview' => 'iframe',
                                                        'iframe_h' => 200,
                                                    ],
                                                ];
                                                $m = $tipoMeta[$archivo->tipo] ?? [
                                                    'label' => ucfirst($archivo->tipo),
                                                    'color' => 'secondary',
                                                    'icon' => 'fa-file',
                                                    'preview' => 'iframe',
                                                    'iframe_h' => 200,
                                                ];
                                                $driveUrl = \App\Services\GoogleDriveService::getUrl($archivo->drive_id);
                                                $previewUrl = \App\Services\GoogleDriveService::getPreviewUrl($archivo->drive_id);
                                            @endphp
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <div class="card h-100 shadow-sm rounded-3 overflow-hidden sisipedia-file-card border-0">
                                                    <div class="rounded-top bg-{{ $m['color'] }}" style="height: 4px;" aria-hidden="true"></div>
                                                    <div class="card-header bg-white border-bottom pt-3 pb-2 px-3">
                                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                                            <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                                                                <span class="badge bg-{{ $m['color'] }} bg-opacity-10 text-{{ $m['color'] }} border border-{{ $m['color'] }} mb-2">
                                                                    <i class="fa {{ $m['icon'] }} me-1"></i>{{ $m['label'] }}
                                                                </span>
                                                                <div class="fw-semibold text-dark small text-break" title="{{ $archivo->nombre_display }}">
                                                                    {{ $archivo->nombre_display }}
                                                                </div>
                                                            </div>
                                                            <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                                                               class="btn btn-sm btn-{{ $m['color'] }} flex-shrink-0">
                                                                <i class="fa fa-download me-1"></i>Descargar
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-0 bg-light">
                                                        @if ($m['preview'] === 'iframe' && !empty($m['iframe_h']))
                                                            <div class="sisipedia-file-preview-wrap position-relative bg-light"
                                                                 data-preview-url="{{ $previewUrl }}"
                                                                 data-is-video="{{ $archivo->tipo === 'video' ? '1' : '0' }}">
                                                                <div class="sisipedia-preview-placeholder d-flex flex-column align-items-center justify-content-center text-center px-3 py-4"
                                                                     style="min-height: {{ max(140, min($m['iframe_h'], 200)) }}px;">
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-{{ $m['color'] }} mb-2 sisipedia-load-category-preview">
                                                                        <i class="fa fa-eye me-1"></i>Ver vista previa
                                                                    </button>
                                                                    <span class="small text-muted">Se carga bajo demanda para ahorrar datos y acelerar la página.</span>
                                                                </div>
                                                                <iframe title="{{ $archivo->nombre_display }}"
                                                                    class="sisipedia-category-iframe w-100 border-0 d-none bg-white"
                                                                    style="height: {{ $m['iframe_h'] }}px;"
                                                                    loading="lazy"
                                                                    allowfullscreen></iframe>
                                                            </div>
                                                        @else
                                                            <div class="d-flex flex-column align-items-center justify-content-center text-center px-3 py-5">
                                                                <span class="rounded-circle bg-{{ $m['color'] }} bg-opacity-10 text-{{ $m['color'] }} d-inline-flex align-items-center justify-content-center mb-3"
                                                                      style="width: 64px; height: 64px;">
                                                                    <i class="fa {{ $m['icon'] }} fa-2x"></i>
                                                                </span>
                                                                <p class="small text-muted mb-3 mb-md-0">
                                                                    @if ($archivo->tipo === 'doc')
                                                                        Vista previa no disponible aquí; descarga el archivo o ábrelo en Drive.
                                                                    @else
                                                                        Abre o descarga el archivo para verlo.
                                                                    @endif
                                                                </p>
                                                                <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                                                                   class="btn btn-outline-{{ $m['color'] }} btn-sm">
                                                                    <i class="fa fa-external-link-alt me-1"></i>Abrir en Google Drive
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- ── Sidebar sticky: registros heredados ──────────────── --}}
                    @php $publicChildren = $category->children->where('is_active', true); @endphp
                    <div class="col-lg-4" style="position: sticky; top: 5.5rem;">
                        <div class="card border-0 rounded-4 overflow-hidden"
                             style="box-shadow: 0 4px 20px rgba(13,110,253,.13); border-top: 3px solid #0d6efd !important;">

                            {{-- Header destacado --}}
                            <div class="px-3 px-md-4 py-3"
                                 style="background: linear-gradient(135deg,#0d6efd 0%,#6610f2 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h2 class="h6 mb-0 text-white fw-bold">
                                        <i class="fa fa-sitemap me-2"></i>Registros heredados
                                    </h2>
                                    @if ($publicChildren->count())
                                        <span class="badge bg-white text-primary fw-bold rounded-pill"
                                              style="font-size:.75rem;">
                                            {{ $publicChildren->count() }}
                                            {{ $publicChildren->count() === 1 ? 'registro' : 'registros' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="card-body p-2 p-md-3">
                                @if ($publicChildren->count())
                                    <div class="d-grid gap-2">
                                        @foreach ($publicChildren as $child)
                                            <a href="{{ route('sisipedia.categories.show', $child) }}"
                                               class="card border text-decoration-none hover-shadow"
                                               style="transition: box-shadow .15s, border-color .15s;">
                                                <div class="d-flex gap-2 align-items-start p-2">
                                                    {{-- Imagen o ícono --}}
                                                    @if ($child->image)
                                                        <img src="{{ asset($child->image) }}" alt="{{ $child->name }}"
                                                            class="rounded flex-shrink-0 mt-1" width="40" height="40"
                                                            style="object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                                            style="width:40px; height:40px; min-width:40px;">
                                                            <i class="fa fa-bookmark"></i>
                                                        </div>
                                                    @endif

                                                    <div class="overflow-hidden flex-grow-1">
                                                        <div class="fw-semibold text-dark text-truncate" style="font-size:.9rem;">
                                                            {{ $child->name }}
                                                        </div>
                                                        @if ($child->description)
                                                            <small class="text-muted d-block text-truncate" style="font-size:.75rem;">
                                                                {{ Str::limit($child->description, 60) }}
                                                            </small>
                                                        @endif
                                                        {{-- Badges de archivos del hijo --}}
                                                        @php $childFiles = $child->relationLoaded('files') ? $child->files : collect(); @endphp
                                                        @if ($childFiles->isNotEmpty())
                                                            <div class="d-flex flex-wrap mt-1" style="gap:.2rem;">
                                                                @if ($childFiles->where('tipo','pdf')->isNotEmpty())
                                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"
                                                                          style="font-size:.6rem; padding:.2rem .4rem;">
                                                                        <i class="fa fa-file-pdf me-1"></i>PDF
                                                                    </span>
                                                                @endif
                                                                @if ($childFiles->where('tipo','doc')->isNotEmpty())
                                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"
                                                                          style="font-size:.6rem; padding:.2rem .4rem;">
                                                                        <i class="fa fa-file-word me-1"></i>Doc
                                                                    </span>
                                                                @endif
                                                                @if ($childFiles->where('tipo','audio')->isNotEmpty())
                                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success"
                                                                          style="font-size:.6rem; padding:.2rem .4rem;">
                                                                        <i class="fa fa-music me-1"></i>Audio
                                                                    </span>
                                                                @endif
                                                                @if ($childFiles->where('tipo','video')->isNotEmpty())
                                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary"
                                                                          style="font-size:.6rem; padding:.2rem .4rem;">
                                                                        <i class="fa fa-video me-1"></i>Video
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Flecha indicadora --}}
                                                    <i class="fa fa-chevron-right text-muted flex-shrink-0 mt-2"
                                                       style="font-size:.75rem;"></i>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="fa fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                        <small>No hay sub-registros en este nivel.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── Aportaciones ─────────────────────────────────────────── --}}
                <div class="row g-4 mt-1">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-3 px-md-4">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <h2 class="h5 mb-0">
                                        <i class="fa fa-layer-group me-2 text-primary"></i>
                                        Aportaciones
                                        @if($category->aportaciones->count())
                                            <span class="badge bg-primary ms-1">{{ $category->aportaciones->count() }}</span>
                                        @endif
                                    </h2>
                                    <button class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" id="btnAbrirRoles"
                                            onclick="mostrarRoles()"
                                            style="border-radius:2rem; background:linear-gradient(135deg,#0d6efd,#6610f2); border:none; letter-spacing:.03em;">
                                        <i class="fa fa-plus-circle me-2"></i>Agregar aportación
                                    </button>
                                </div>
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="fa fa-info-circle me-1"></i>¿Conoces algo sobre este tema? ¡Comparte tu conocimiento con la comunidad!
                                </p>
                            </div>

                            {{-- Paso 1: Selector de rol --}}
                            <div id="rolSelector" class="px-3 px-md-4 pt-3 pb-2" style="display:none;">
                                <p class="fw-semibold mb-3 text-center">¿Cómo deseas aportar?</p>
                                <div class="row g-3 justify-content-center mb-3">
                                    {{-- Docente --}}
                                    <div class="col-12 col-sm-4">
                                        <div class="card border-2 rounded-4 text-center p-3 h-100 rol-card"
                                             style="cursor:pointer; border-color:#0d6efd !important; transition: transform .15s, box-shadow .15s;"
                                             onclick="seleccionarRol('Docente')">
                                            <div class="mb-2">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                                                      style="width:56px;height:56px;">
                                                    <i class="fa fa-chalkboard-teacher fa-lg"></i>
                                                </span>
                                            </div>
                                            <div class="fw-bold fs-6">Docente</div>
                                            <small class="text-muted">Aporto como educador</small>
                                        </div>
                                    </div>
                                    {{-- Líder --}}
                                    <div class="col-12 col-sm-4">
                                        <div class="card border-2 rounded-4 text-center p-3 h-100 rol-card"
                                             style="cursor:pointer; border-color:#198754 !important; transition: transform .15s, box-shadow .15s;"
                                             onclick="seleccionarRol('Líder')">
                                            <div class="mb-2">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white"
                                                      style="width:56px;height:56px;">
                                                    <i class="fa fa-star fa-lg"></i>
                                                </span>
                                            </div>
                                            <div class="fw-bold fs-6">Líder</div>
                                            <small class="text-muted">Aporto como líder comunitario</small>
                                        </div>
                                    </div>
                                    {{-- Niño/Estudiante --}}
                                    <div class="col-12 col-sm-4">
                                        <div class="card border-2 rounded-4 text-center p-3 h-100 rol-card"
                                             style="cursor:pointer; border-color:#fd7e14 !important; transition: transform .15s, box-shadow .15s;"
                                             onclick="seleccionarRol('Niño/Estudiante')">
                                            <div class="mb-2">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white"
                                                      style="width:56px;height:56px;background:#fd7e14;">
                                                    <i class="fa fa-child fa-lg"></i>
                                                </span>
                                            </div>
                                            <div class="fw-bold fs-6">Niño / Estudiante</div>
                                            <small class="text-muted">Aporto como estudiante</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="cancelarAportacion()">
                                        <i class="fa fa-times me-1"></i>Cancelar
                                    </button>
                                </div>
                            </div>

                            {{-- Paso 2: Formulario de aportación --}}
                            <div id="formAportacionWrap" class="px-3 px-md-4 pt-3" style="display:none;">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                                    </div>
                                @endif

                                {{-- Badge con rol elegido --}}
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge fs-6 px-3 py-2" id="rolBadge" style="background:#0d6efd;">
                                        <i class="fa fa-user me-1"></i><span id="rolTexto"></span>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-link text-muted p-0"
                                            onclick="volverARoles()">
                                        <i class="fa fa-arrow-left me-1"></i>Cambiar rol
                                    </button>
                                </div>

                                <form action="{{ route('sisipedia.categories.aportaciones.store', $category) }}"
                                      method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="rol_nombre" id="inputRolNombre"
                                           value="{{ old('rol_nombre') }}">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nombre en lengua original <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre_ol" class="form-control @error('nombre_ol') is-invalid @enderror"
                                                   value="{{ old('nombre_ol') }}" required>
                                            @error('nombre_ol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Institución</label>
                                            <input type="text" name="institucion" class="form-control @error('institucion') is-invalid @enderror"
                                                   value="{{ old('institucion') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Ubicación</label>
                                            <input type="text" name="ubicacion" class="form-control @error('ubicacion') is-invalid @enderror"
                                                   value="{{ old('ubicacion') }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Detalle</label>
                                            <textarea name="detalle" rows="3"
                                                      class="form-control @error('detalle') is-invalid @enderror">{{ old('detalle') }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="fa fa-file-pdf text-danger me-1"></i>PDF
                                            </label>
                                            <input type="file" name="pdf" accept=".pdf"
                                                   class="form-control @error('pdf') is-invalid @enderror">
                                            @error('pdf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="fa fa-file-word text-primary me-1"></i>Documento Word (.doc/.docx)
                                            </label>
                                            <input type="file" name="doc" accept=".doc,.docx"
                                                   class="form-control @error('doc') is-invalid @enderror">
                                            @error('doc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="fa fa-music text-success me-1"></i>Audio
                                            </label>
                                            <input type="file" name="audio" accept=".mp3,.wav,.ogg"
                                                   class="form-control @error('audio') is-invalid @enderror">
                                            @error('audio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="fa fa-video text-warning me-1"></i>Video
                                            </label>
                                            <input type="file" name="video" accept=".mp4,.webm,.mov"
                                                   class="form-control @error('video') is-invalid @enderror">
                                            @error('video')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mb-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save me-1"></i>Guardar aportación
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="cancelarAportacion()">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                                <hr>
                            </div>

                            {{-- Lista de aportaciones --}}
                            <div class="card-body p-3 p-md-4">
                                @forelse($category->aportaciones as $aportacion)
                                    <div class="card border rounded-3 mb-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                        <h3 class="h6 mb-0 fw-bold">{{ $aportacion->nombre_ol }}</h3>
                                                        @if($aportacion->rol_nombre)
                                                            @php
                                                                $rolColor = match($aportacion->rol_nombre) {
                                                                    'Docente'         => 'primary',
                                                                    'Líder'           => 'success',
                                                                    'Niño/Estudiante' => 'warning',
                                                                    default           => 'secondary',
                                                                };
                                                                $rolIcono = match($aportacion->rol_nombre) {
                                                                    'Docente'         => 'fa-chalkboard-teacher',
                                                                    'Líder'           => 'fa-star',
                                                                    'Niño/Estudiante' => 'fa-child',
                                                                    default           => 'fa-user',
                                                                };
                                                            @endphp
                                                            <span class="badge bg-{{ $rolColor }} bg-opacity-10 text-{{ $rolColor }} border border-{{ $rolColor }}">
                                                                <i class="fa {{ $rolIcono }} me-1"></i>{{ $aportacion->rol_nombre }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                                        @if($aportacion->institucion)
                                                            <span class="badge bg-light text-dark border">
                                                                <i class="fa fa-building me-1"></i>{{ $aportacion->institucion }}
                                                            </span>
                                                        @endif
                                                        @if($aportacion->ubicacion)
                                                            <span class="badge bg-light text-dark border">
                                                                <i class="fa fa-map-marker-alt me-1"></i>{{ $aportacion->ubicacion }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($aportacion->detalle)
                                                        <p class="text-muted small mb-2">{{ $aportacion->detalle }}</p>
                                                    @endif

                                                    {{-- Archivos de la aportación --}}
                                                    @if($aportacion->pdf || $aportacion->doc || $aportacion->audio || $aportacion->video)
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @if($aportacion->pdf)
                                                                <a href="{{ \App\Services\GoogleDriveService::getUrl($aportacion->pdf) }}"
                                                                   target="_blank" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fa fa-file-pdf me-1"></i>PDF
                                                                </a>
                                                            @endif
                                                            @if($aportacion->doc)
                                                                <a href="{{ \App\Services\GoogleDriveService::getUrl($aportacion->doc) }}"
                                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fa fa-file-word me-1"></i>Word
                                                                </a>
                                                            @endif
                                                            @if($aportacion->audio)
                                                                <button class="btn btn-sm btn-outline-success"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#audio-{{ $aportacion->id }}">
                                                                    <i class="fa fa-music me-1"></i>Audio
                                                                </button>
                                                            @endif
                                                            @if($aportacion->video)
                                                                <button class="btn btn-sm btn-outline-warning"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#video-{{ $aportacion->id }}">
                                                                    <i class="fa fa-video me-1"></i>Video
                                                                </button>
                                                            @endif
                                                        </div>

                                                        @if($aportacion->audio)
                                                            <div class="collapse mt-2 sisipedia-aportacion-collapse" id="audio-{{ $aportacion->id }}">
                                                                <iframe title="Audio — {{ $aportacion->nombre_ol }}"
                                                                        class="sisipedia-aportacion-iframe w-100 border-0 rounded-3"
                                                                        style="height:160px;"
                                                                        loading="lazy"
                                                                        data-defer-src="{{ \App\Services\GoogleDriveService::getPreviewUrl($aportacion->audio) }}"
                                                                        data-media="audio"
                                                                        allowfullscreen></iframe>
                                                            </div>
                                                        @endif
                                                        @if($aportacion->video)
                                                            <div class="collapse mt-2 sisipedia-aportacion-collapse" id="video-{{ $aportacion->id }}">
                                                                <iframe title="Video — {{ $aportacion->nombre_ol }}"
                                                                        class="sisipedia-aportacion-iframe w-100 border-0 rounded-3"
                                                                        style="height:320px;"
                                                                        loading="lazy"
                                                                        data-defer-src="{{ \App\Services\GoogleDriveService::getPreviewUrl($aportacion->video) }}"
                                                                        data-media="video"
                                                                        allowfullscreen></iframe>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>

                                                @hasanyrole('admin|sisicha')
                                                <form action="{{ route('sisipedia.categories.aportaciones.destroy', [$category, $aportacion]) }}"
                                                      method="POST" onsubmit="return confirm('¿Eliminar esta aportación?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endhasanyrole
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                        No hay aportaciones para este registro aún.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    const rolColors = {
                        'Docente':         '#0d6efd',
                        'Líder':           '#198754',
                        'Niño/Estudiante': '#fd7e14',
                    };

                    function mostrarRoles() {
                        document.getElementById('rolSelector').style.display = 'block';
                        document.getElementById('formAportacionWrap').style.display = 'none';
                        document.getElementById('btnAbrirRoles').style.display = 'none';
                    }

                    function seleccionarRol(rol) {
                        document.getElementById('inputRolNombre').value = rol;
                        document.getElementById('rolTexto').textContent  = rol;
                        document.getElementById('rolBadge').style.background = rolColors[rol] ?? '#6c757d';
                        document.getElementById('rolSelector').style.display = 'none';
                        document.getElementById('formAportacionWrap').style.display = 'block';
                    }

                    function volverARoles() {
                        document.getElementById('formAportacionWrap').style.display = 'none';
                        document.getElementById('rolSelector').style.display = 'block';
                    }

                    function cancelarAportacion() {
                        document.getElementById('rolSelector').style.display = 'none';
                        document.getElementById('formAportacionWrap').style.display = 'none';
                        document.getElementById('btnAbrirRoles').style.display = '';
                    }

                    // Si hay errores de validación, re-abrir el formulario con el rol que se envió
                    @if($errors->any() && old('rol_nombre'))
                        window.addEventListener('DOMContentLoaded', function() {
                            seleccionarRol('{{ old('rol_nombre') }}');
                        });
                    @endif

                    /** Vista previa de archivos de categoría: iframe solo tras pulsar el botón */
                    document.addEventListener('click', function (e) {
                        var btn = e.target.closest('.sisipedia-load-category-preview');
                        if (!btn) return;
                        e.preventDefault();
                        var wrap = btn.closest('.sisipedia-file-preview-wrap');
                        if (!wrap || wrap.dataset.previewLoaded === '1') return;
                        var iframe = wrap.querySelector('.sisipedia-category-iframe');
                        var url = wrap.dataset.previewUrl;
                        if (!iframe || !url) return;
                        iframe.src = url;
                        if (wrap.dataset.isVideo === '1') {
                            iframe.setAttribute('allow', 'autoplay; fullscreen');
                        }
                        iframe.classList.remove('d-none');
                        iframe.classList.add('d-block');
                        wrap.dataset.previewLoaded = '1';
                        var ph = wrap.querySelector('.sisipedia-preview-placeholder');
                        if (ph) ph.classList.add('d-none');
                    });

                    /** Aportaciones: iframe de audio/video solo al expandir el collapse */
                    window.addEventListener('DOMContentLoaded', function () {
                        document.querySelectorAll('.sisipedia-aportacion-collapse').forEach(function (collapseEl) {
                            collapseEl.addEventListener('shown.bs.collapse', function () {
                                var iframe = collapseEl.querySelector('iframe.sisipedia-aportacion-iframe[data-defer-src]');
                                if (!iframe || iframe.dataset.loaded === '1') return;
                                var url = iframe.getAttribute('data-defer-src');
                                if (!url) return;
                                iframe.src = url;
                                if (iframe.dataset.media === 'video') {
                                    iframe.setAttribute('allow', 'autoplay; fullscreen');
                                }
                                iframe.dataset.loaded = '1';
                            });
                        });
                    });
                </script>

                <style>
                    /* Borde derecho en audio/video solo en md+ */
                    @media (min-width: 768px) {
                        .border-end-md { border-right: 1px solid #dee2e6 !important; }
                    }
                    /* Hover en cards de hijos */
                    .hover-shadow:hover {
                        box-shadow: 0 4px 12px rgba(0,0,0,.1) !important;
                        border-color: #0d6efd !important;
                    }
                </style>

            </div>
        </div>
    </div>
@endsection
