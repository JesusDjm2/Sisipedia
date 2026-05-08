<li>
    <div class="category-item">
        <div class="category-info">
            @if ($category->children->count())
                <button class="expand-btn" data-target="children-{{ $category->id }}">
                    <i class="fa fa-chevron-right"></i>
                </button>
            @else
                <div style="width: 28px;"></div>
            @endif
            @if ($category->image)
                <img src="{{ asset($category->image) }}" alt="{{ $category->display_name }}" class="category-image">
            @else
                <div class="category-image-placeholder">
                    <i class="fa fa-folder"></i>
                </div>
            @endif
            <div class="category-details">
                <div class="category-name">
                    <span class="text-primary fw-bold mr-2">{{ $category->numbering }}</span>
                    <span class="cat-name-text">{{ $category->display_name }}</span>
                </div>
                @if ($category->slug)
                    <div class="category-slug">{{ $category->slug }}</div>
                @endif
                {{-- Badges de archivos y aportaciones --}}
                @php
                    $catFiles  = $category->relationLoaded('files') ? $category->files : collect();
                    $aportCount = $aportCounts[$category->id] ?? 0;
                @endphp
                <div class="mt-1 d-flex flex-wrap" style="gap:.3rem;">
                    @if ($catFiles->where('tipo','pdf')->isNotEmpty())
                        <span class="badge badge-danger" style="font-size:.65rem;">
                            <i class="fa fa-file-pdf-o mr-1"></i>PDF
                        </span>
                    @endif
                    @if ($catFiles->where('tipo','doc')->isNotEmpty())
                        <span class="badge badge-secondary" style="font-size:.65rem;">
                            <i class="fa fa-file-word-o mr-1"></i>Doc
                        </span>
                    @endif
                    @if ($catFiles->where('tipo','audio')->isNotEmpty())
                        <span class="badge badge-success" style="font-size:.65rem;">
                            <i class="fa fa-music mr-1"></i>Audio
                        </span>
                    @endif
                    @if ($catFiles->where('tipo','video')->isNotEmpty())
                        <span class="badge badge-primary" style="font-size:.65rem;">
                            <i class="fa fa-video-camera mr-1"></i>Video
                        </span>
                    @endif
                    @if ($aportCount > 0)
                        <span class="badge badge-warning text-dark" style="font-size:.65rem;">
                            <i class="fa fa-users mr-1"></i>{{ $aportCount }} aportacion{{ $aportCount !== 1 ? 'es' : '' }}
                        </span>
                    @else
                        <span class="badge badge-light text-muted border" style="font-size:.65rem;">
                            Sin aportaciones
                        </span>
                    @endif
                    @if ($category->children->count())
                        <span class="badge badge-secondary" style="font-size:.65rem;">
                            <i class="fa fa-folder-open mr-1"></i>{{ $category->children->count() }} sub-registro{{ $category->children->count() !== 1 ? 's' : '' }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="category-meta">
                @if ($category->parent)
                    <span class="category-parent">
                        <i class="fa fa-level-up-alt fa-rotate-90"></i>
                        {{ $category->parent->display_name }}
                    </span>
                @endif

                <span class="category-order">
                    <i class="fas fa-sort-numeric-down"></i> Orden: {{ $category->order }}
                </span>
                <span class="category-status {{ $category->is_active ? 'status-active' : 'status-inactive' }}">
                    <i class="fa fa-{{ $category->is_active ? 'check-circle' : 'times-circle' }}"></i>
                    {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
        </div>

        <div class="category-actions">
            <a href="{{ $category->is_active ? route('sisipedia.categories.show', $category) : route('sisipedia.categories.admin-show', $category) }}"
                target="_blank" class="btn btn-sm btn-info btn-icon" title="Ver detalles">
                <i class="fa fa-eye"></i>
            </a>
            <a href="{{ route('sisipedia.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary btn-icon"
                title="Editar registro">
                <i class="fa fa-edit"></i>
            </a>
            <button type="button" class="btn btn-sm btn-primary btn-icon toggle-status-btn"
                data-id="{{ $category->id }}" data-active="{{ $category->is_active ? '1' : '0' }}"
                data-toggle-url="{{ route('sisipedia.categories.toggle-status', $category) }}"
                title="{{ $category->is_active ? 'Desactivar' : 'Activar' }}">
                <i class="fa fa-{{ $category->is_active ? 'ban' : 'check' }}"></i>
            </button>
            @php $subtreeSize = $category->subtreeSize(); @endphp
            <button type="button"
                class="btn btn-sm btn-icon delete-category {{ $category->children->count() ? 'btn-outline-danger' : 'btn-danger' }}"
                data-id="{{ $category->id }}" data-name="{{ $category->display_name }}"
                data-delete-url="{{ route('sisipedia.categories.destroy', $category) }}"
                data-cascade="{{ $category->children->count() ? '1' : '0' }}" data-subtree-size="{{ $subtreeSize }}"
                title="{{ $category->children->count() ? 'Eliminar este registro y toda su jerarquía (herencias)' : 'Eliminar' }}">
                <i class="fa fa-trash"></i>@if ($category->children->count())
                    <span class="d-none d-xl-inline small ml-1">+ jerarquía</span>
                @endif
            </button>
        </div>
    </div>

    @if ($category->children->count())
        <ul id="children-{{ $category->id }}" style="display:none;">
            @foreach ($category->children as $child)
                @include('sisichakuna.categorias.tree-item', ['category' => $child])
            @endforeach
        </ul>
    @endif
</li>
