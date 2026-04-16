@extends('layouts.app')
@section('titulo', 'Categorías')
@section('contenido')
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fa fa-sitemap me-2"></i> Estructura Sisipedia
                    </h3>
                    <a href="{{ route('sisipedia.categories.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> Nueva Categoría
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Buscador en tiempo real -->
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fa fa-search text-muted"></i>
                        </span>
                        <input type="text" id="searchCategory" class="form-control form-control-sm"
                            placeholder="Buscar categoría por nombre...">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="fa fa-info-circle"></i> La búsqueda filtrará categorías y mostrará su jerarquía
                    </small>
                </div>
                <!-- Estructura jerárquica de categorías -->
                <div id="categoryTree">
                    @if ($tree->count())
                        <ul class="category-tree list-unstyled">
                            @foreach ($tree as $category)
                                @include('sisichakuna.categorias.tree-item', [
                                    'category' => $category,
                                    'level' => 0,
                                ])
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-folder-open fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No hay categorías creadas</p>
                            <a href="{{ route('sisipedia.categories.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Crear primera categoría
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .category-tree ul {
            padding-left: 2rem;
            list-style: none;
            margin-top: 0.5rem;
        }

        .category-tree li {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .category-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .category-item:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .category-item.has-search-match {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .category-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .category-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: #f3f4f6;
        }

        .category-image-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
        }

        .category-details {
            flex: 1;
        }

        .category-name {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .category-slug {
            font-size: 0.75rem;
            color: #6b7280;
            font-family: monospace;
        }

        .category-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .category-parent {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .category-parent i {
            margin-right: 0.25rem;
        }

        .category-order {
            font-size: 0.75rem;
            color: #6b7280;
            background: #f3f4f6;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .category-status {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 500;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .category-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .expand-btn {
            background: none;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s;
        }

        .expand-btn:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .expand-btn i {
            font-size: 0.875rem;
        }

        .tree-line {
            position: absolute;
            left: -1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchCategory');
            const clearButton = document.getElementById('clearSearch');
            const categoryItems = document.querySelectorAll('.category-item');
            const csrfToken = '{{ csrf_token() }}';

            function submitPostForm(action, method = null) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = action;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = csrfToken;
                form.appendChild(csrf);

                if (method) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = method;
                    form.appendChild(methodInput);
                }

                document.body.appendChild(form);
                form.submit();
            }

            // Función para buscar y filtrar
            function filterCategories(searchTerm) {
                if (!searchTerm.trim()) {
                    // Mostrar todo y limpiar highlights
                    categoryItems.forEach(item => {
                        item.classList.remove('has-search-match');
                        item.closest('li').style.display = '';
                    });

                    // Mostrar todos los ULs
                    document.querySelectorAll('.category-tree ul').forEach(ul => {
                        ul.style.display = '';
                    });
                    return;
                }

                const termLower = searchTerm.toLowerCase().trim();
                let hasMatches = false;

                categoryItems.forEach(item => {
                    const categoryName = item.querySelector('.category-name')?.textContent.toLowerCase() ||
                        '';
                    const categorySlug = item.querySelector('.category-slug')?.textContent.toLowerCase() ||
                        '';
                    const matches = categoryName.includes(termLower) || categorySlug.includes(termLower);

                    if (matches) {
                        hasMatches = true;
                        item.classList.add('has-search-match');
                        // Mostrar el item y todos sus padres
                        let parent = item.closest('li');
                        while (parent) {
                            parent.style.display = '';
                            // Expandir los padres para mostrar el item encontrado
                            const parentUl = parent.querySelector('ul');
                            if (parentUl) {
                                parentUl.style.display = '';
                            }
                            parent = parent.parentElement?.closest('li');
                        }
                    } else {
                        item.classList.remove('has-search-match');
                        // Ocultar el item
                        item.closest('li').style.display = 'none';
                    }
                });

                // Mostrar mensaje si no hay resultados
                const existingNoResults = document.querySelector('.no-results');
                if (!hasMatches && searchTerm.trim()) {
                    if (!existingNoResults) {
                        const noResultsDiv = document.createElement('div');
                        noResultsDiv.className = 'no-results';
                        noResultsDiv.innerHTML = `
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p>No se encontraron categorías con "<strong>${searchTerm}</strong>"</p>
                        <small class="text-muted">Intenta con otro término de búsqueda</small>
                    `;
                        document.getElementById('categoryTree').appendChild(noResultsDiv);
                    } else {
                        existingNoResults.innerHTML = `
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p>No se encontraron categorías con "<strong>${searchTerm}</strong>"</p>
                        <small class="text-muted">Intenta con otro término de búsqueda</small>
                    `;
                        existingNoResults.style.display = '';
                    }
                } else if (existingNoResults) {
                    existingNoResults.style.display = 'none';
                }
            }

            // Evento de búsqueda en tiempo real
            let searchTimeout;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterCategories(e.target.value);
                }, 300);
            });

            // Botón limpiar búsqueda
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                filterCategories('');
                searchInput.focus();
            });

            // Funcionalidad expandir/colapsar
            document.querySelectorAll('.expand-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const targetId = this.dataset.target;
                    const targetUl = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (targetUl) {
                        if (targetUl.style.display === 'none') {
                            targetUl.style.display = '';
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-down');
                        } else {
                            targetUl.style.display = 'none';
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-right');
                        }
                    }
                });
            });

            document.querySelectorAll('.toggle-status-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const toggleUrl = this.dataset.toggleUrl;
                    const isActive = this.dataset.active === '1';
                    const actionText = isActive ? 'desactivar' : 'activar';

                    Swal.fire({
                        title: 'Confirmar cambio',
                        text: `¿Deseas ${actionText} este registro?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: `Sí, ${actionText}`,
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitPostForm(toggleUrl);
                        }
                    });
                });
            });

            document.querySelectorAll('.delete-category').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const categoryName = this.dataset.name;
                    const deleteUrl = this.dataset.deleteUrl;

                    Swal.fire({
                        title: 'Eliminar registro',
                        text: `¿Estás seguro de eliminar "${categoryName}"? Esta acción no se puede deshacer.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitPostForm(deleteUrl, 'DELETE');
                        }
                    });
                });
            });
        });
    </script>

@endsection
