@extends('layouts.app')
@section('titulo', '- Aportaciones Sisipedia')
@section('contenido')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0">Aportaciones</h4>
        <small class="text-muted">Registros enviados por el equipo, docentes, líderes y estudiantes</small>
    </div>
    <a href="{{ route('sisipedia.categories.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left mr-1"></i>Categorías
    </a>
</div>

@if($pendientesGeneral > 0)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <i class="fa fa-hourglass-half mr-2"></i>
        Hay <strong>{{ $pendientesGeneral }}</strong> aporte(s) general(es) sin categoría pendiente(s) de aprobación.
    </div>
@endif

@php
    $rolMeta = [
        'Equipo Puklla'   => ['color' => 'info', 'icon' => 'fa-users'],
        'Docente'         => ['color' => 'primary',   'icon' => 'fa-graduation-cap'],
        'Líder'           => ['color' => 'success',   'icon' => 'fa-star'],
        'Niño/Estudiante' => ['color' => 'warning',   'icon' => 'fa-child'],
    ];
@endphp

{{-- ── Tarjetas de resumen ───────────────────────────────────── --}}
<div class="row mb-4 justify-content-center">
    <div class="col-6 col-lg-2 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1">Total</div>
                <div class="h3 mb-0 font-weight-bold">{{ $aportaciones->total() }}</div>
            </div>
        </div>
    </div>
    @foreach($roles as $rol)
        <div class="col-6 col-lg-2 mb-3">
            <div class="card border-0 shadow-sm h-100 border-left border-{{ $rolMeta[$rol]['color'] ?? 'secondary' }}"
                 style="border-left: 4px solid !important;">
                <div class="card-body text-center py-3">
                    <div class="text-muted small mb-1">
                        <i class="fa {{ $rolMeta[$rol]['icon'] ?? 'fa-user' }} mr-1"></i>{{ $rol }}
                    </div>
                    <div class="h3 mb-0 font-weight-bold text-{{ $rolMeta[$rol]['color'] ?? 'secondary' }}">
                        {{ $totals[$rol] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ── Filtros ───────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('sisipedia.aportaciones.index') }}" class="form-inline flex-wrap" style="gap:.5rem;">
            <div class="form-group mr-2 mb-2">
                <label class="sr-only">Buscar</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                    </div>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control" placeholder="Nombre, institución, ubicación…" style="min-width:200px;">
                </div>
            </div>

            <div class="form-group mr-2 mb-2">
                <label class="sr-only">Rol</label>
                <select name="rol" class="form-control form-control-sm">
                    <option value="">— Todos los roles —</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol }}" @selected(request('rol') === $rol)>{{ $rol }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mr-2 mb-2">
                <label class="sr-only">Categoría</label>
                <select name="category_id" class="form-control form-control-sm" style="min-width:200px;">
                    <option value="">— Todas las categorías —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                            {{ $cat->parent ? $cat->parent->name.' › ' : '' }}{{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mr-2 mb-2">
                <label class="sr-only">Estado</label>
                <select name="estado" class="form-control form-control-sm">
                    <option value="">— Todos los estados —</option>
                    <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendiente de aprobación</option>
                    <option value="aprobada" @selected(request('estado') === 'aprobada')>Aprobada</option>
                </select>
            </div>

            <div class="mb-2">
                <button type="submit" class="btn btn-sm btn-primary mr-1">
                    <i class="fa fa-filter mr-1"></i>Filtrar
                </button>
                @if(request()->hasAny(['q','rol','category_id','estado']))
                    <a href="{{ route('sisipedia.aportaciones.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-times mr-1"></i>Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Tabla ─────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($aportaciones->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                No hay aportaciones que coincidan con los filtros.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:1%">#</th>
                        <th>Nombre en lengua original</th>
                        <th>Rol</th>
                        <th>Registro (categoría)</th>
                        <th class="text-center">Estado</th>
                        <th>Institución / Ubicación</th>
                        <th class="text-center">Archivos</th>
                        <th>Fecha</th>
                        <th style="width:1%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aportaciones as $ap)
                    @php
                        $cat  = $ap->category;
                        $path = [];
                        $cur  = $cat;
                        while ($cur) { array_unshift($path, $cur); $cur = $cur->parent ?? null; }
                    @endphp
                    <tr>
                        <td class="text-muted small align-middle">{{ $ap->id }}</td>

                        {{-- Nombre + detalle colapsable --}}
                        <td class="align-middle">
                            <div class="font-weight-semibold">{{ $ap->nombre_ol }}</div>
                            @if($ap->detalle)
                                <small class="text-muted d-block" style="max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                       title="{{ $ap->detalle }}">
                                    {{ $ap->detalle }}
                                </small>
                            @endif
                        </td>

                        {{-- Rol --}}
                        <td class="align-middle">
                            <span class="badge badge-{{ $rolMeta[$ap->rol_nombre]['color'] ?? 'secondary' }}">
                                <i class="fa {{ $rolMeta[$ap->rol_nombre]['icon'] ?? 'fa-user' }} mr-1"></i>
                                {{ $ap->rol_nombre }}
                            </span>
                        </td>

                        {{-- Ruta de categoría --}}
                        <td class="align-middle" style="max-width:220px;">
                            @if($cat)
                                <div class="d-flex flex-wrap align-items-center" style="gap:.2rem;">
                                    @foreach($path as $i => $node)
                                        @if($i > 0)
                                            <i class="fa fa-angle-right text-muted" style="font-size:.7rem;"></i>
                                        @endif
                                        <a href="{{ route('sisipedia.categories.admin-show', $node) }}"
                                           class="badge badge-light border text-dark font-weight-normal"
                                           style="font-size:.75rem;">
                                            {{ $node->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="badge badge-light border text-muted small font-weight-normal">Sin registro</span>
                            @endif
                        </td>

                        <td class="align-middle text-center">
                            @if($ap->is_approved)
                                <span class="badge badge-success">Aprobada</span>
                            @else
                                <span class="badge badge-warning text-dark">Pendiente</span>
                            @endif
                        </td>

                        {{-- Institución / Ubicación --}}
                        <td class="align-middle small">
                            @if($ap->institucion)
                                <div><i class="fa fa-building text-muted mr-1"></i>{{ $ap->institucion }}</div>
                            @endif
                            @if($ap->ubicacion)
                                <div><i class="fa fa-map-marker text-muted mr-1"></i>{{ $ap->ubicacion }}</div>
                            @endif
                            @if(!$ap->institucion && !$ap->ubicacion)
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Archivos --}}
                        <td class="align-middle text-center">
                            <div class="d-flex justify-content-center flex-wrap" style="gap:.25rem;">
                                @if($ap->pdf)
                                    <a href="{{ \App\Services\GoogleDriveService::getUrl($ap->pdf) }}"
                                       target="_blank" class="badge badge-danger" title="PDF">
                                        <i class="fa fa-file-pdf-o"></i>
                                    </a>
                                @endif
                                @if($ap->doc)
                                    <a href="{{ \App\Services\GoogleDriveService::getUrl($ap->doc) }}"
                                       target="_blank" class="badge badge-primary" title="Word">
                                        <i class="fa fa-file-word-o"></i>
                                    </a>
                                @endif
                                @if($ap->audio)
                                    <a href="{{ \App\Services\GoogleDriveService::getUrl($ap->audio) }}"
                                       target="_blank" class="badge badge-success" title="Audio">
                                        <i class="fa fa-music"></i>
                                    </a>
                                @endif
                                @if($ap->video)
                                    <a href="{{ \App\Services\GoogleDriveService::getUrl($ap->video) }}"
                                       target="_blank" class="badge badge-warning" title="Video">
                                        <i class="fa fa-video-camera"></i>
                                    </a>
                                @endif
                                @if(!$ap->pdf && !$ap->doc && !$ap->audio && !$ap->video)
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </td>

                        {{-- Fecha --}}
                        <td class="align-middle small text-nowrap text-muted">
                            {{ $ap->created_at->format('d/m/Y') }}
                        </td>

                        {{-- Acciones --}}
                        <td class="align-middle text-right text-nowrap">
                            @if($cat)
                                <a href="{{ route('sisipedia.categories.admin-show', $cat) }}"
                                   class="btn btn-xs btn-outline-secondary mr-1" title="Ver en categoría">
                                    <i class="fa fa-eye"></i>
                                </a>
                            @endif
                            @if(!$ap->is_approved)
                                <form action="{{ route('sisipedia.aportaciones.approve', $ap) }}" method="POST" class="d-inline mr-1">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success" title="Aprobar">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                            @endif
                            @if($cat)
                                <form action="{{ route('sisipedia.categories.aportaciones.destroy', [$cat, $ap]) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta aportación?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('sisipedia.aportaciones.destroy', $ap) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta aportación?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($aportaciones->hasPages())
            <div class="px-3 py-2 border-top">
                {{ $aportaciones->links() }}
            </div>
        @endif
        @endif
    </div>
</div>

@endsection
