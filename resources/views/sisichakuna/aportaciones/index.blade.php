@extends('layouts.app')
@section('titulo', '- Aportaciones Sisipedia')
@section('contenido')
@include('sisichakuna.partials.sisipedia-admin-nav', ['active' => 'aportaciones'])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-1">
                    <i class="fa fa-users mr-2 text-primary"></i>Aportaciones
                </h4>
                <small class="text-muted">Registros enviados por el equipo, docentes, líderes y estudiantes</small>
            </div>
            <div class="d-flex align-items-center" style="gap:.5rem;">
                <span class="badge badge-light border text-muted font-weight-normal" style="font-size:.8rem;">
                    {{ $aportaciones->total() }} total
                </span>
                @if($pendientesGeneral > 0)
                    <span class="badge badge-warning text-dark font-weight-normal" style="font-size:.8rem;">
                        <i class="fa fa-exclamation-circle mr-1"></i>{{ $pendientesGeneral }} pendiente(s)
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

@if($pendientesGeneral > 0)
    <div class="alert alert-warning border-0 shadow-sm mb-4 sisipedia-aportes-alert d-flex align-items-start">
        <span class="rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center mr-3 flex-shrink-0 sisipedia-aportes-alert-icon">
            <i class="fa fa-link"></i>
        </span>
        <div>
            <div class="font-weight-bold mb-1">Pendientes de vincular a un registro</div>
            <p class="mb-0 small text-dark">
                Hay <strong>{{ $pendientesGeneral }}</strong> aporte(s) desde la portada sin registro asignado.
                Usa <strong>Vincular registro</strong> en la tabla para elegir la ficha Sisipedia donde se publicará el material.
            </p>
        </div>
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
                           class="form-control" placeholder="Nombre, título, institución, lugar…" style="min-width:200px;">
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
                            {{ $cat->parent ? $cat->parent->display_name.' › ' : '' }}{{ $cat->display_name }}
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
                        <th>Título</th>
                        <th>Rol</th>
                        <th>Registro (categoría)</th>
                        <th class="text-center">Estado</th>
                        <th>Institución / Trabajo / Ubicación</th>
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
                                <div class="text-muted small mt-1"
                                     style="max-width:280px;max-height:60px;overflow:hidden;line-height:1.4;"
                                     title="{{ strip_tags($ap->detalle) }}">
                                    {!! $ap->detalle !!}
                                </div>
                            @endif
                        </td>
                        <td class="align-middle small">
                            @if($ap->titulo)
                                <span class="badge badge-info">{{ $ap->titulo }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Rol --}}
                        <td class="align-middle">
                            <span class="badge badge-{{ $rolMeta[$ap->rol_nombre]['color'] ?? 'secondary' }}">
                                <i class="fa {{ $rolMeta[$ap->rol_nombre]['icon'] ?? 'fa-user' }} mr-1"></i>
                                {{ \App\Models\sisipedia\Aportacion::etiquetaRol($ap->rol_nombre) }}
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
                                            {{ $node->display_name }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="badge badge-light border text-muted small font-weight-normal">Sin registro</span>
                                @if(!$ap->is_approved)
                                    <span class="badge badge-warning text-dark ml-1" style="font-size:.65rem;">Requiere asignación</span>
                                @endif
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
                            @if($ap->lugar_trabajo)
                                <div><i class="fa fa-briefcase text-muted mr-1"></i>{{ $ap->lugar_trabajo }}</div>
                            @endif
                            @if($ap->ubicacion)
                                <div><i class="fa fa-map-marker text-muted mr-1"></i>{{ $ap->ubicacion }}</div>
                            @endif
                            @if(!$ap->institucion && !$ap->lugar_trabajo && !$ap->ubicacion)
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
                                @if($ap->imagen)
                                    <a href="{{ \App\Services\GoogleDriveService::getUrl($ap->imagen) }}"
                                       target="_blank" title="Ver imagen en Drive">
                                        <img src="{{ \App\Services\GoogleDriveService::getThumbnailUrl($ap->imagen, 60) }}"
                                             alt="Imagen" width="60" height="60"
                                             style="object-fit:cover;border-radius:4px;border:1px solid #dee2e6;"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block';">
                                        <span class="badge badge-info d-none"><i class="fa fa-image"></i></span>
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
                                @if(!$ap->imagen && !$ap->pdf && !$ap->doc && !$ap->audio && !$ap->video)
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
                            <button type="button"
                                    class="btn btn-xs btn-outline-primary mr-1 sisipedia-btn-editar-ap"
                                    data-toggle="modal"
                                    data-target="#modalEditarAporte"
                                    data-action="{{ route('sisipedia.aportaciones.update', $ap) }}"
                                    data-nombre="{{ e($ap->nombre_ol) }}"
                                    data-rol="{{ $ap->rol_nombre }}"
                                    data-titulo="{{ e($ap->titulo ?? '') }}"
                                    data-institucion="{{ e($ap->institucion ?? '') }}"
                                    data-lugar-trabajo="{{ e($ap->lugar_trabajo ?? '') }}"
                                    data-ubicacion="{{ e($ap->ubicacion ?? '') }}"
                                    data-detalle="{{ e($ap->detalle ?? '') }}"
                                    data-category-id="{{ $ap->category_id ?? '' }}"
                                    data-imagen-id="{{ $ap->imagen ?? '' }}"
                                    title="Editar aportación">
                                <i class="fa fa-edit"></i>
                            </button>
                            @if(!$ap->category_id)
                                <button type="button"
                                        class="btn btn-sm mr-1 sisipedia-btn-aprobar font-weight-bold"
                                        style="background:#f59e0b;color:#fff;border:none;box-shadow:0 2px 6px rgba(245,158,11,.45);letter-spacing:.02em;"
                                        data-toggle="modal"
                                        data-target="#modalAprobarAporte"
                                        data-action="{{ route('sisipedia.aportaciones.approve', $ap) }}"
                                        data-nombre="{{ e($ap->nombre_ol) }}"
                                        data-rol="{{ e(\App\Models\sisipedia\Aportacion::etiquetaRol($ap->rol_nombre)) }}"
                                        data-needs-category="1"
                                        title="Sin registro asignado — haz clic para vincular">
                                    <i class="fa fa-link mr-1"></i>Vincular registro
                                </button>
                            @elseif(!$ap->is_approved)
                                <button type="button"
                                        class="btn btn-sm btn-success mr-1 sisipedia-btn-aprobar"
                                        data-toggle="modal"
                                        data-target="#modalAprobarAporte"
                                        data-action="{{ route('sisipedia.aportaciones.approve', $ap) }}"
                                        data-nombre="{{ e($ap->nombre_ol) }}"
                                        data-rol="{{ e(\App\Models\sisipedia\Aportacion::etiquetaRol($ap->rol_nombre)) }}"
                                        data-needs-category="0"
                                        title="Aprobar aportación">
                                    <i class="fa fa-check-circle mr-1"></i>Aprobar
                                </button>
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

{{-- Modal: Editar aportación --}}
<div class="modal fade" id="modalEditarAporte" tabindex="-1" role="dialog" aria-labelledby="modalEditarAporteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form method="POST" id="formEditarAporte" action="#" class="modal-content border-0 shadow-lg" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="modal-header border-0 pb-0 text-white"
                 style="background: linear-gradient(135deg, #1a6e3c 0%, #28a745 45%, #20c997 100%); border-radius: .25rem .25rem 0 0; padding: 1.25rem 1.5rem;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-1" id="modalEditarAporteTitulo">
                        <i class="fa fa-edit mr-2"></i>Editar aportación
                    </h5>
                    <p class="mb-0 small" style="opacity:.92;">Modifica los datos o cambia el registro (categoría) asignado.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">
                            Nombre en lengua original <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre_ol" id="editNombreOl"
                               class="form-control" required maxlength="255">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">Rol <span class="text-danger">*</span></label>
                        <select name="rol_nombre" id="editRolNombre" class="form-control" required>
                            @foreach(App\Models\sisipedia\Aportacion::ROLES as $rol)
                                <option value="{{ $rol }}">{{ App\Models\sisipedia\Aportacion::etiquetaRol($rol) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">Título</label>
                        <input type="text" name="titulo" id="editTitulo"
                               class="form-control" maxlength="255">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">Institución</label>
                        <input type="text" name="institucion" id="editInstitucion"
                               class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">Lugar de trabajo</label>
                        <input type="text" name="lugar_trabajo" id="editLugarTrabajo"
                               class="form-control" maxlength="255">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">Ubicación</label>
                        <input type="text" name="ubicacion" id="editUbicacion"
                               class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="font-weight-bold small text-dark d-block mb-1">Detalle / Descripción</label>
                    <textarea name="detalle" id="editDetalle" class="form-control summernote-editor" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="font-weight-bold small text-dark d-block mb-1">
                        Imagen (opcional, reemplaza la actual)
                    </label>
                    <div id="editImagenPreviewWrap" class="mb-2 d-none">
                        <a id="editImagenPreviewLink" href="#" target="_blank" rel="noopener">
                            <img id="editImagenPreview" src="" alt="Imagen actual"
                                 style="max-height:100px;border-radius:6px;border:1px solid #dee2e6;"
                                 onerror="document.getElementById('editImagenPreviewWrap').classList.add('d-none');">
                        </a>
                        <small class="text-muted d-block mt-1">Imagen actual — sube un archivo nuevo para reemplazarla.</small>
                    </div>
                    <input type="file" name="imagen" id="editImagen" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif">
                </div>
                <hr class="my-3">
                <div class="mb-2">
                    <label class="font-weight-bold small text-dark d-block mb-1">
                        <i class="fa fa-sitemap text-primary mr-1"></i>Registro (categoría) asignado
                    </label>
                    <select name="category_id" id="editCategoryId" class="form-control">
                        <option value="">— Sin registro asignado (pendiente) —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">
                                {{ $cat->parent ? $cat->parent->display_name.' › ' : '' }}{{ $cat->display_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted mt-1 d-block">
                        <i class="fa fa-info-circle mr-1"></i>Cambiar el registro moverá esta aportación a la ficha seleccionada.
                    </small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4 font-weight-semibold">
                    <i class="fa fa-save mr-2"></i>Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formEditarAporte');
    document.querySelectorAll('.sisipedia-btn-editar-ap').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!form) return;
            form.setAttribute('action', btn.getAttribute('data-action'));
            document.getElementById('editNombreOl').value    = btn.getAttribute('data-nombre') || '';
            document.getElementById('editTitulo').value      = btn.getAttribute('data-titulo') || '';
            document.getElementById('editInstitucion').value = btn.getAttribute('data-institucion') || '';
            document.getElementById('editLugarTrabajo').value = btn.getAttribute('data-lugar-trabajo') || '';
            document.getElementById('editUbicacion').value   = btn.getAttribute('data-ubicacion') || '';
            var detalleContent = btn.getAttribute('data-detalle') || '';
            var imagenId = btn.getAttribute('data-imagen-id') || '';
            document.getElementById('editImagen').value = '';

            var previewWrap = document.getElementById('editImagenPreviewWrap');
            var previewImg  = document.getElementById('editImagenPreview');
            var previewLink = document.getElementById('editImagenPreviewLink');
            if (imagenId && previewWrap && previewImg && previewLink) {
                previewImg.src  = 'https://drive.google.com/thumbnail?id=' + imagenId + '&sz=w200';
                previewLink.href = 'https://drive.google.com/file/d/' + imagenId + '/view';
                previewWrap.classList.remove('d-none');
            } else if (previewWrap) {
                previewWrap.classList.add('d-none');
            }

            var rolSel = document.getElementById('editRolNombre');
            if (rolSel) rolSel.value = btn.getAttribute('data-rol') || '';

            var catSel = document.getElementById('editCategoryId');
            if (catSel) catSel.value = btn.getAttribute('data-category-id') || '';

            // Cargar contenido en Summernote después de que el modal se muestre
            setTimeout(function() {
                if (window.jQuery && $('.summernote-editor').length) {
                    $('.summernote-editor').summernote('code', detalleContent);
                }
            }, 100);
        });
    });

    // Inicializar Summernote editor para el campo de detalle
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery) {
            $('.summernote-editor').summernote({
                height: 120,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview']]
                ],
                placeholder: 'Describe los detalles de esta aportación...',
                callbacks: {
                    onInit: function() {
                        $('.note-editable').css('font-size', '14px');
                    }
                }
            });
        }
    });
});
</script>

{{-- Modal: asistente de vinculación y aprobación (un solo modal reutilizable) --}}
<div class="modal fade sisipedia-modal-aprobar" id="modalAprobarAporte" tabindex="-1" role="dialog" aria-labelledby="modalAprobarAporteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <form method="POST" id="formModalAprobarAporte" action="#" class="modal-content border-0 shadow-lg sisipedia-modal-aprobar-content">
            @csrf
            <div class="modal-header border-0 pb-0 sisipedia-modal-aprobar-header text-white">
                <div>
                    <h5 class="modal-title font-weight-bold mb-1" id="modalAprobarAporteTitulo">
                        <i class="fa fa-map-signs mr-2"></i>Aprobar aportación
                    </h5>
                    <p class="mb-0 small sisipedia-modal-aprobar-sub">Confirma el destino público del material en Sisipedia.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-3">
                <div class="sisipedia-aporte-resumen card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="text-uppercase text-muted small font-weight-bold mb-2" style="letter-spacing:.04em;">Vas a aprobar</div>
                        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:.75rem;">
                            <div>
                                <div class="h6 mb-1 font-weight-bold text-dark" id="modalAprobarNombre">—</div>
                                <span class="badge badge-secondary" id="modalAprobarRol">—</span>
                            </div>
                            <div class="text-muted small text-right">
                                <i class="fa fa-info-circle mr-1"></i>Visible para visitantes tras confirmar
                            </div>
                        </div>
                    </div>
                </div>

                <div id="bloqueElegirRegistro">
                    <input type="hidden" id="modalPickerCategoryId" value="">

                    <label for="fnBuscarRegistro" class="font-weight-bold text-dark mb-1 d-block">
                        <i class="fa fa-search text-primary mr-1"></i>Buscar en el árbol de registros
                    </label>
                    <input type="text"
                           class="form-control mb-2"
                           id="fnBuscarRegistro"
                           placeholder="Nombre del registro o parte de la ruta (ej. tema, padre › hijo)…"
                           autocomplete="off">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                        <small class="form-text text-muted mb-0">
                            Los <strong>registros raíz</strong> agrupan a los <strong>hijos</strong> (indentados). Pulsa una fila para elegir.
                        </small>
                        <span class="badge badge-light border text-muted font-weight-normal" id="fnRegistroContador" style="font-size:.7rem;"></span>
                    </div>

                    <label class="font-weight-bold text-dark mb-1 d-block">
                        <i class="fa fa-sitemap text-primary mr-1"></i>Elegir registro destino
                    </label>
                    <div class="sisipedia-reg-pick-scroll border rounded position-relative bg-white">
                        @forelse($categoryPickerRows ?? [] as $row)
                            <button type="button"
                                    class="sisipedia-reg-pick-row w-100 text-left border-0 {{ $row['is_root'] ? 'sisipedia-reg-pick-root' : 'sisipedia-reg-pick-child' }}"
                                    data-cat-id="{{ $row['id'] }}"
                                    data-depth="{{ $row['depth'] }}"
                                    data-search="{{ e($row['search_blob']) }}"
                                    style="--reg-depth: {{ $row['depth'] }};">
                                <span class="sisipedia-reg-pick-row-inner d-flex align-items-start w-100">
                                    <span class="sisipedia-reg-pick-check flex-shrink-0 mr-2 mt-1">
                                        <i class="fa fa-circle-o text-muted sisipedia-reg-ico-off" aria-hidden="true"></i>
                                        <i class="fa fa-check-circle text-success sisipedia-reg-ico-on d-none" aria-hidden="true"></i>
                                    </span>
                                    <span class="flex-grow-1 min-w-0">
                                        @if($row['is_root'])
                                            <span class="badge badge-primary align-middle mr-1" style="font-size:.65rem;">Raíz</span>
                                        @endif
                                        <span class="sisipedia-reg-pick-name font-weight-bold d-block">{{ $row['name'] }}</span>
                                        <span class="sisipedia-reg-pick-path small text-muted d-block text-truncate" title="{{ $row['path_label'] }}">{{ $row['path_label'] }}</span>
                                    </span>
                                </span>
                            </button>
                        @empty
                            <div class="p-4 text-center text-muted small">No hay categorías configuradas.</div>
                        @endforelse
                    </div>
                    <p class="text-danger small mb-0 mt-2 d-none" id="fnRegistroSinCoincidencias">
                        <i class="fa fa-exclamation-triangle mr-1"></i>No hay registros que coincidan. Prueba otras palabras clave o borra el filtro.
                    </p>
                    <p class="text-muted small mt-3 mb-0">
                        <i class="fa fa-lightbulb-o text-warning mr-1"></i>
                        El aporte se mostrará en la ficha pública del registro elegido (el mismo nivel que ves en «Registros» del sitio).
                    </p>
                </div>

                <div id="bloqueSoloConfirmar" class="d-none alert alert-light border mb-0">
                    <i class="fa fa-check text-success mr-2"></i>
                    Este aporte ya tiene un registro asociado. Solo confirma la aprobación para publicarlo.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4 font-weight-semibold" id="btnModalAprobarSubmit">
                    <i class="fa fa-check mr-2"></i><span id="btnModalAprobarSubmitTexto">Confirmar y publicar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .sisipedia-aportes-alert-icon { width: 2.5rem; height: 2.5rem; font-size: 1.1rem; }
    .sisipedia-modal-aprobar-header {
        background: linear-gradient(135deg, #1a5f9e 0%, #0d6efd 45%, #5b21b6 100%);
        border-radius: 0.25rem 0.25rem 0 0;
        padding: 1.25rem 1.5rem;
    }
    .sisipedia-modal-aprobar-sub { opacity: 0.92; }
    .sisipedia-modal-aprobar-content { border-radius: 0.35rem; overflow: hidden; }
    .sisipedia-aporte-resumen { background: linear-gradient(180deg, #f8fafc 0%, #fff 100%); }
    .sisipedia-reg-pick-scroll {
        max-height: min(58vh, 520px);
        min-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }
    .sisipedia-reg-pick-row {
        display: block;
        padding: 0.65rem 0.85rem 0.65rem calc(0.85rem + var(--reg-depth, 0) * 1.35rem);
        background: #fff;
        border-bottom: 1px solid #e9ecef !important;
        transition: background .12s ease, box-shadow .12s ease;
        cursor: pointer;
        line-height: 1.35;
    }
    .sisipedia-reg-pick-row:last-child { border-bottom: 0 !important; }
    .sisipedia-reg-pick-row:hover {
        background: #f1f7ff !important;
    }
    .sisipedia-reg-pick-row:focus {
        outline: none;
        box-shadow: inset 0 0 0 2px rgba(13, 110, 253, 0.35);
        z-index: 1;
        position: relative;
    }
    .sisipedia-reg-pick-root {
        background: linear-gradient(90deg, #e7f1ff 0%, #f8fafc 12%, #fff 100%) !important;
        border-bottom: 1px solid #cfe2ff !important;
    }
    .sisipedia-reg-pick-root .sisipedia-reg-pick-name { font-size: 1rem; }
    .sisipedia-reg-pick-child { border-left: 3px solid #dee2e6; }
    .sisipedia-reg-pick-child[data-depth="1"] { border-left-color: #adb5bd; }
    .sisipedia-reg-pick-row.is-selected {
        background: #e8f5e9 !important;
        box-shadow: inset 3px 0 0 0 #28a745;
    }
    .sisipedia-reg-pick-row.is-selected .sisipedia-reg-ico-off { display: none !important; }
    .sisipedia-reg-pick-row.is-selected .sisipedia-reg-ico-on { display: inline-block !important; }
    .sisipedia-reg-pick-path { font-size: 0.78rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
(function () {
    var form = document.getElementById('formModalAprobarAporte');
    var inputBuscar = document.getElementById('fnBuscarRegistro');
    var hiddenCat = document.getElementById('modalPickerCategoryId');
    var pickRows = document.querySelectorAll('.sisipedia-reg-pick-row');
    var bloqueElegir = document.getElementById('bloqueElegirRegistro');
    var bloqueSolo = document.getElementById('bloqueSoloConfirmar');
    var msgVacío = document.getElementById('fnRegistroSinCoincidencias');
    var contador = document.getElementById('fnRegistroContador');
    var btnSubmitTexto = document.getElementById('btnModalAprobarSubmitTexto');

    function limpiarSeleccionRegistro() {
        if (hiddenCat) {
            hiddenCat.value = '';
            hiddenCat.removeAttribute('name');
        }
        pickRows.forEach(function (row) {
            row.classList.remove('is-selected');
        });
    }

    function seleccionarRegistro(row) {
        if (!hiddenCat) return;
        var id = row.getAttribute('data-cat-id');
        hiddenCat.value = id;
        pickRows.forEach(function (r) {
            r.classList.toggle('is-selected', r === row);
        });
    }

    function aplicarFiltroRegistros() {
        if (!inputBuscar) return;
        var q = (inputBuscar.value || '').trim().toLowerCase();
        var visible = 0;
        pickRows.forEach(function (row) {
            var blob = (row.getAttribute('data-search') || '').toLowerCase();
            var match = !q || blob.indexOf(q) !== -1;
            row.classList.toggle('d-none', !match);
            if (match) visible++;
        });
        if (hiddenCat && hiddenCat.value) {
            var sel = document.querySelector('.sisipedia-reg-pick-row.is-selected');
            if (sel && sel.classList.contains('d-none')) {
                limpiarSeleccionRegistro();
            }
        }
        if (msgVacío) {
            msgVacío.classList.toggle('d-none', !(q && visible === 0));
        }
        if (contador) {
            contador.textContent = visible + ' visible(s)';
        }
    }

    pickRows.forEach(function (row) {
        row.addEventListener('click', function () {
            seleccionarRegistro(row);
        });
    });

    if (inputBuscar) {
        inputBuscar.addEventListener('input', aplicarFiltroRegistros);
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            var needs = bloqueElegir && !bloqueElegir.classList.contains('d-none');
            if (needs && hiddenCat && !hiddenCat.value) {
                e.preventDefault();
                alert('Elige un registro destino en la lista (raíz o hijo).');
                return false;
            }
        });
    }

    function abrirModalAsistente(event) {
        var btn = event.relatedTarget;
        if (!btn || !form) return;

        var action = btn.getAttribute('data-action');
        if (action) form.setAttribute('action', action);

        var nombreEl = document.getElementById('modalAprobarNombre');
        var rolEl = document.getElementById('modalAprobarRol');
        if (nombreEl) nombreEl.textContent = btn.getAttribute('data-nombre') || '—';
        if (rolEl) {
            rolEl.textContent = btn.getAttribute('data-rol') || '—';
            rolEl.className = 'badge badge-secondary';
        }

        var needs = btn.getAttribute('data-needs-category') === '1';
        if (inputBuscar) inputBuscar.value = '';
        pickRows.forEach(function (row) {
            row.classList.remove('d-none');
        });
        aplicarFiltroRegistros();

        if (needs) {
            bloqueElegir.classList.remove('d-none');
            bloqueSolo.classList.add('d-none');
            limpiarSeleccionRegistro();
            if (hiddenCat) hiddenCat.setAttribute('name', 'category_id');
            if (btnSubmitTexto) btnSubmitTexto.textContent = 'Vincular registro y publicar';
        } else {
            bloqueElegir.classList.add('d-none');
            bloqueSolo.classList.remove('d-none');
            limpiarSeleccionRegistro();
            if (btnSubmitTexto) btnSubmitTexto.textContent = 'Confirmar y publicar';
        }
    }

    if (window.jQuery) {
        window.jQuery('#modalAprobarAporte').on('show.bs.modal', abrirModalAsistente);
        window.jQuery('#modalAprobarAporte').on('hidden.bs.modal', function () {
            if (form) form.setAttribute('action', '#');
            limpiarSeleccionRegistro();
        });
    } else {
        var modal = document.getElementById('modalAprobarAporte');
        if (modal) {
            modal.addEventListener('show.bs.modal', abrirModalAsistente);
            modal.addEventListener('hidden.bs.modal', function () {
                if (form) form.setAttribute('action', '#');
                limpiarSeleccionRegistro();
            });
        }
    }
})();
});
</script>

@endsection
