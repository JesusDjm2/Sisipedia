@extends('layouts.padre')
@section('titulo', '- Sisipedia')
@section('contenido')
<div class="fixed-image-container">
    <div class="overlay"></div>
    <div class="centered-content">
        <h2>SISIPEDIA</h2>
        <h1>Repositorio de Trabajos</h1>
    </div>
</div>
<div class="container py-2 py-md-3">
    <div class="row mt-2 mt-md-5">
        <div class="col-12 mb-3">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text" id="searchCategory" class="form-control"
                    placeholder="Buscar categoría por nombre o descripción...">
                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <small class="text-muted mt-1 d-block">
                <i class="fa fa-info-circle"></i> Busca y navega por herencias de forma rápida.
            </small>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-2 p-md-3">
                    <div id="categoryTree">
                        @if ($tree->count())
                            <ul class="list-unstyled mb-0">
                                @foreach ($tree as $category)
                                    @include('sisichakuna.tree', [
                                        'category' => $category,
                                        'level' => 0,
                                    ])
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-folder-open fa-4x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No hay categorías disponibles</p>
                            </div>
                        @endif
                    </div>
                    <div id="noResults" class="alert alert-light border text-center mt-3 mb-0 d-none">
                        <i class="fa fa-search me-1"></i>No se encontraron categorías con ese término.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchCategory');
        const clearButton = document.getElementById('clearSearch');
        const noResults = document.getElementById('noResults');
        const nodes = Array.from(document.querySelectorAll('[data-category-item]'));

        function showAll() {
            nodes.forEach((node) => {
                const row = node.closest('.category-node');
                if (row) row.classList.remove('d-none');
            });

            document.querySelectorAll('[data-tree-children]').forEach((list) => {
                list.classList.add('show');
            });

            noResults.classList.add('d-none');
        }

        function filterCategories(term) {
            const search = term.trim().toLowerCase();

            if (!search) {
                showAll();
                return;
            }

            let matches = 0;
            nodes.forEach((node) => {
                const row = node.closest('.category-node');
                if (!row) return;

                const text = node.dataset.searchable || '';
                const isMatch = text.includes(search);

                row.classList.toggle('d-none', !isMatch);

                if (isMatch) {
                    matches++;
                    let parent = row.parentElement ? row.parentElement.closest('.category-node') : null;
                    while (parent) {
                        parent.classList.remove('d-none');
                        const parentList = parent.querySelector('[data-tree-children]');
                        if (parentList) parentList.classList.add('show');
                        parent = parent.parentElement ? parent.parentElement.closest('.category-node') : null;
                    }
                }
            });

            noResults.classList.toggle('d-none', matches > 0);
        }

        let debounce = null;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounce);
            debounce = setTimeout(() => filterCategories(e.target.value), 200);
        });

        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            showAll();
            searchInput.focus();
        });
    });
</script>
@endsection