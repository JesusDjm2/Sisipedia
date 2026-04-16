<li>
    <div class="category-item">
        <div class="category-info">
            @if ($category->children->count())
                <button class="expand-btn" data-target="children-{{ $category->id }}">
                    <i class="fa fa-chevron-down"></i>
                </button>
            @else
                <div style="width: 28px;"></div>
            @endif
            @if ($category->image)
                <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" class="category-image">
            @else
                <div class="category-image-placeholder">
                    <i class="fa fa-folder"></i>
                </div>
            @endif
            <div class="category-details">
                <div class="category-name">
                    <span class="text-primary fw-bold me-2">{{ $category->numbering }}</span>
                    {{ $category->name }}
                </div>
                @if ($category->slug)
                    <div class="category-slug">{{ $category->slug }}</div>
                @endif
            </div>
            <div class="category-meta">
                @if ($category->parent)
                    <span class="category-parent">
                        <i class="fa fa-level-up-alt fa-rotate-90"></i>
                        {{ $category->parent->name }}
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
            <a href="{{ route('sisipedia.categories.edit', $category) }}" class="btn btn-sm btn-warning btn-icon"
                title="Editar">
                <i class="fa fa-edit"></i>
            </a>
            <button type="button" class="btn btn-sm btn-primary btn-icon toggle-status-btn"
                data-id="{{ $category->id }}" data-active="{{ $category->is_active ? '1' : '0' }}"
                data-toggle-url="{{ route('sisipedia.categories.toggle-status', $category) }}"
                title="{{ $category->is_active ? 'Desactivar' : 'Activar' }}">
                <i class="fa fa-{{ $category->is_active ? 'ban' : 'check' }}"></i>
            </button>
            @if (!$category->children->count())
                <button type="button" class="btn btn-sm btn-danger btn-icon delete-category"
                    data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                    data-delete-url="{{ route('sisipedia.categories.destroy', $category) }}" title="Eliminar">
                    <i class="fa fa-trash"></i>
                </button>
            @else
                <button type="button" class="btn btn-sm btn-secondary btn-icon" disabled
                    title="No se puede eliminar porque tiene subcategorías">
                    <i class="fa fa-trash"></i>
                </button>
            @endif
        </div>
    </div>

    @if ($category->children->count())
        <ul id="children-{{ $category->id }}">
            @foreach ($category->children as $child)
                @include('sisichakuna.categorias.tree-item', ['category' => $child])
            @endforeach
        </ul>
    @endif
</li>

