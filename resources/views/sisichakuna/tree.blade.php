@php
    $publicChildren = $category->children->where('is_active', true);
    $hasChildren    = $publicChildren->count() > 0;
    $catFiles       = $category->files ?? collect();
    $hasFiles       = $catFiles->isNotEmpty();
    $aportCount     = $category->aportaciones_count ?? $category->aportaciones()->count();

    $indentClass = match(true) {
        $level === 0 => '',
        $level === 1 => 'ms-3 ms-md-4',
        default      => 'ms-4 ms-md-5',
    };
@endphp

<li class="mb-2 category-node {{ $indentClass }}">
    {{-- ── Fila principal ───────────────────────────────────────── --}}
    <div class="list-group-item border rounded-3 px-3 py-2"
         data-category-item
         data-searchable="{{ strtolower($category->display_name . ' ' . ($category->description ?? '')) }}">

        <div class="d-flex align-items-center gap-2">

            {{-- Miniatura / icono --}}
            @if ($category->image)
                <img src="{{ asset($category->image) }}" alt="{{ $category->display_name }}"
                     class="rounded-circle flex-shrink-0 d-none d-sm-block"
                     width="40" height="40" loading="lazy" decoding="async"
                     style="object-fit:cover;">
            @else
                <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center
                            justify-content-center flex-shrink-0 d-none d-sm-block"
                     style="width:40px;height:40px;min-width:40px;">
                    <i class="fa fa-folder{{ $hasChildren ? '-open' : '' }} fa-sm"></i>
                </div>
            @endif

            {{-- Nombre + descripción + badges --}}
            <div class="flex-grow-1 min-width-0" style="min-width:0;">
                <div class="d-flex flex-wrap align-items-center gap-1 mb-1">
                    <span class="fw-semibold text-dark" style="word-break:break-word;">
                        <span class="badge bg-primary me-2">{{ $category->numbering }}</span>
                        {{ $category->display_name }}
                    </span>

                    {{-- Badges de archivos --}}
                    @if ($catFiles->where('tipo','pdf')->isNotEmpty())
                        <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger"
                              style="font-size:.65rem;">
                            <i class="fa fa-file-pdf-o me-1"></i>PDF
                        </span>
                    @endif
                    @if ($catFiles->where('tipo','doc')->isNotEmpty())
                        <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary"
                              style="font-size:.65rem;">
                            <i class="fa fa-file-word-o me-1"></i>Doc
                        </span>
                    @endif
                    @if ($catFiles->where('tipo','audio')->isNotEmpty())
                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success"
                              style="font-size:.65rem;">
                            <i class="fa fa-music me-1"></i>Audio
                        </span>
                    @endif
                    @if ($catFiles->where('tipo','video')->isNotEmpty())
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary"
                              style="font-size:.65rem;">
                            <i class="fa fa-video-camera me-1"></i>Video
                        </span>
                    @endif

                    {{-- Aportaciones --}}
                    @if ($aportCount > 0)
                        <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning"
                              style="font-size:.65rem;" title="{{ $aportCount }} aportaciones">
                            <i class="fa fa-users me-1"></i>{{ $aportCount }}
                        </span>
                    @endif

                    {{-- Hijos --}}
                    @if ($hasChildren)
                        <span class="badge rounded-pill bg-light text-secondary border"
                              style="font-size:.65rem;">
                            <i class="fa fa-folder-open me-1"></i>{{ $publicChildren->count() }}
                        </span>
                    @endif
                </div>

                @if ($category->description)
                    <small class="text-muted d-block"
                           style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;">
                        {{ Str::limit($category->description, 120) }}
                    </small>
                @endif
            </div>

            {{-- Botones --}}
            <div class="d-flex gap-1 flex-shrink-0 ms-auto">
                <a href="{{ route('sisipedia.categories.show', $category) }}"
                   class="btn btn-sm btn-outline-primary px-2" title="Ver detalle">
                    <i class="fa fa-eye"></i>
                    <span class="d-none d-md-inline ms-1">Ver</span>
                </a>
                @if ($hasChildren)
                    <button class="btn btn-sm btn-outline-secondary px-2 toggle-btn"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#sub-{{ $category->id }}"
                            aria-expanded="false"
                            title="Expandir / colapsar">
                        <i class="fa fa-chevron-right toggle-icon"></i>
                    </button>
                @endif
            </div>

        </div>
    </div>

    {{-- ── Hijos colapsables (cerrados por defecto) ─────────────── --}}
    @if ($hasChildren)
        <ul id="sub-{{ $category->id }}"
            class="list-unstyled mt-1 collapse"
            data-tree-children>
            @foreach ($publicChildren->sortBy('order') as $child)
                @include('sisichakuna.tree', [
                    'category' => $child,
                    'level'    => $level + 1,
                ])
            @endforeach
        </ul>
    @endif
</li>
