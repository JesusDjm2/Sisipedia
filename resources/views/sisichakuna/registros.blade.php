@extends('layouts.padre')
@section('titulo', '- Categorías Sisipedia')
@section('contenido')
    <div class="container py-5 mt-5">
        <div class="row mt-5">
            <div class="col-12">
                <!-- Header -->
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-primary mb-3">Sisipedia</h1>
                    <p class="lead text-muted">Explora nuestro catálogo de categorías y recursos bibliográficos</p>
                    <hr class="w-25 mx-auto">
                </div>

                <!-- Buscador -->
                <div class="row justify-content-center mb-5">
                    <div class="col-md-8">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fa fa-search text-muted"></i>
                            </span>
                            <input type="text" id="searchCategoryPublic" class="form-control border-start-0 ps-0"
                                placeholder="Buscar categoría por nombre..." style="box-shadow: none;">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearchPublic">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block text-center">
                            <i class="fa fa-info-circle"></i> La búsqueda filtrará categorías y mostrará su jerarquía
                        </small>
                    </div>
                </div>

                <!-- Estructura jerárquica de categorías con acordeones -->
                <div id="categoryTreePublic">
                    @if ($tree->count())
                        <div class="row">
                            <div class="col-12">
                                @foreach ($tree as $category)
                                    <div class="category-item-public mb-3" data-name="{{ strtolower($category->display_name) }}">
                                        <div class="accordion" id="accordion-{{ $category->id }}">
                                            <div class="accordion-item border-0 shadow-sm">
                                                <div class="accordion-header">
                                                    <div class="card border-0 hover-shadow">
                                                        <div class="card-body p-0">
                                                            <button
                                                                class="btn w-100 text-start p-3 d-flex align-items-start justify-content-between"
                                                                type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapse-{{ $category->id }}"
                                                                aria-expanded="true"
                                                                aria-controls="collapse-{{ $category->id }}">
                                                                <div class="d-flex align-items-start flex-grow-1">
                                                                    <!-- Icono o imagen -->
                                                                    <div class="flex-shrink-0">
                                                                        @if ($category->image)
                                                                            <img src="{{ asset($category->image) }}"
                                                                                alt="{{ $category->display_name }}"
                                                                                class="rounded-circle"
                                                                                style="width: 50px; height: 50px; object-fit: cover;">
                                                                        @else
                                                                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                                                style="width: 50px; height: 50px;">
                                                                                <i
                                                                                    class="fa fa-folder-open fa-2x text-primary"></i>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <!-- Contenido -->
                                                                    <div class="flex-grow-1 ms-3">
                                                                        <h5 class="mb-1">
                                                                            <span
                                                                                class="badge bg-primary me-2">{{ $category->numbering }}</span>
                                                                            {{ $category->display_name }}
                                                                        </h5>
                                                                        @if ($category->description)
                                                                            <p class="text-muted mb-2">
                                                                                {{ Str::limit($category->description, 150) }}
                                                                            </p>
                                                                        @endif
                                                                        <div class="d-flex gap-3 small text-muted">
                                                                            <span>
                                                                                <i class="fa fa-tag me-1"></i>
                                                                                Slug: {{ $category->slug }}
                                                                            </span>
                                                                            <span>
                                                                                <i class="fa fa-sort-numeric-down me-1"></i>
                                                                                Orden: {{ $category->order }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <i class="fa fa-chevron-down accordion-icon"></i>
                                                                </div>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Contenido colapsable (subcategorías) -->
                                                <div id="collapse-{{ $category->id }}"
                                                    class="accordion-collapse collapse show"
                                                    data-bs-parent="#accordion-{{ $category->id }}">
                                                    <div class="accordion-body p-3 bg-light">
                                                        @if ($category->children->count())
                                                            <div class="ms-3">
                                                                @foreach ($category->children as $child)
                                                                    @include('sisichakuna.category-child', [
                                                                        'category' => $child,
                                                                        'level' => 1,
                                                                    ])
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <p class="text-muted text-center mb-0">
                                                                <i class="fa fa-info-circle me-1"></i>
                                                                No hay subcategorías disponibles.
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-folder-open fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No hay categorías disponibles en este momento.</p>
                        </div>
                    @endif
                </div>

                <!-- Mensaje sin resultados -->
                <div id="noResultsPublic" class="text-center py-5" style="display: none;">
                    <i class="fa fa-search fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron categorías</h5>
                    <p class="text-muted">Intenta con otro término de búsqueda</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .category-item-public {
            transition: all 0.2s ease;
        }

        .accordion-button:focus {
            box-shadow: none;
        }

        .accordion-button:not(.collapsed) {
            background-color: transparent;
            box-shadow: none;
        }

        .accordion-icon {
            transition: transform 0.3s ease;
        }

        button[aria-expanded="true"] .accordion-icon {
            transform: rotate(180deg);
        }

        .btn:focus {
            box-shadow: none;
        }

        .border-start {
            border-left-width: 2px !important;
        }

        .accordion-item {
            background: transparent;
        }

        .accordion-button {
            padding: 0;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchCategoryPublic');
            const clearButton = document.getElementById('clearSearchPublic');
            const noResultsDiv = document.getElementById('noResultsPublic');
            const categoryItems = document.querySelectorAll('.category-item-public');

            // Función para expandir todos los acordeones de una categoría
            function expandAccordion(categoryItem) {
                const collapseElement = categoryItem.querySelector('.accordion-collapse');
                const button = categoryItem.querySelector('button');
                if (collapseElement && button && !collapseElement.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(collapseElement, {
                        show: true
                    });
                }
            }

            // Función para mostrar/ocultar según búsqueda
            function filterCategories(searchTerm) {
                if (!searchTerm.trim()) {
                    // Mostrar todo
                    categoryItems.forEach(item => {
                        item.style.display = '';
                    });
                    noResultsDiv.style.display = 'none';
                    return;
                }

                const termLower = searchTerm.toLowerCase().trim();
                let hasMatches = false;

                categoryItems.forEach(item => {
                    const categoryName = item.querySelector('h5')?.textContent.toLowerCase() || '';
                    const categoryDesc = item.querySelector('p')?.textContent.toLowerCase() || '';
                    const matches = categoryName.includes(termLower) || categoryDesc.includes(termLower);

                    if (matches) {
                        hasMatches = true;
                        item.style.display = '';
                        // Expandir el acordeón de la categoría encontrada
                        expandAccordion(item);
                        // Mostrar todos los padres
                        let parent = item.parentElement?.closest('.category-item-public');
                        while (parent) {
                            parent.style.display = '';
                            expandAccordion(parent);
                            parent = parent.parentElement?.closest('.category-item-public');
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });

                noResultsDiv.style.display = hasMatches ? 'none' : 'block';
            }

            // Búsqueda en tiempo real
            let searchTimeout;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterCategories(e.target.value);
                }, 300);
            });

            // Limpiar búsqueda
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                filterCategories('');
                searchInput.focus();
            });
        });
    </script>
@endsection
