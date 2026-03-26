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
            <a href="{{ route('public.sisi', $category) }}" target="_blank" class="btn btn-sm btn-info btn-icon"
                title="Ver detalles">
                <i class="fa fa-eye"></i>
            </a>
            <a href="{{ route('sisipedia.categories.edit', $category) }}" class="btn btn-sm btn-warning btn-icon"
                title="Editar">
                <i class="fa fa-edit"></i>
            </a>
            <button type="button" class="btn btn-sm btn-primary btn-icon toggle-status-btn"
                data-id="{{ $category->id }}" data-active="{{ $category->is_active ? '1' : '0' }}"
                title="{{ $category->is_active ? 'Desactivar' : 'Activar' }}">
                <i class="fa fa-{{ $category->is_active ? 'ban' : 'check' }}"></i>
            </button>
            @if (!$category->children->count())
                <button type="button" class="btn btn-sm btn-danger btn-icon delete-category"
                    data-id="{{ $category->id }}" data-name="{{ $category->name }}" title="Eliminar">
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle de estado
            document.querySelectorAll('.toggle-status-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const categoryId = this.dataset.id;
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action =
                        `{{ route('sisipedia.categories.toggle-status', '') }}/${categoryId}`;
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    document.body.appendChild(form);
                    form.submit();
                });
            });

            // Eliminar categoría
            document.querySelectorAll('.delete-category').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const categoryId = this.dataset.id;
                    const categoryName = this.dataset.name;

                    if (confirm(`¿Estás seguro de eliminar la categoría "${categoryName}"?`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action =
                            `{{ route('sisipedia.categories.destroy', '') }}/${categoryId}`;
                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';
                        const method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'DELETE';
                        form.appendChild(csrf);
                        form.appendChild(method);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
