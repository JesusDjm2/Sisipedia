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
            <div class="d-flex flex-column flex-sm-row gap-2">
                <div class="input-group flex-grow-1">
                    <span class="input-group-text bg-white">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                    <input type="text" id="searchCategory" class="form-control"
                        placeholder="Buscar categoría por nombre o descripción...">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <button class="btn btn-sm btn-outline-secondary" id="expandAll" title="Expandir todo">
                        <i class="fa fa-expand me-1"></i>
                        <span class="d-none d-sm-inline">Expandir</span>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="collapseAll" title="Colapsar todo">
                        <i class="fa fa-compress me-1"></i>
                        <span class="d-none d-sm-inline">Colapsar</span>
                    </button>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <small class="text-muted me-1">Contiene:</small>
                <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger" style="font-size:.65rem;">
                    <i class="fa fa-file-pdf-o me-1"></i>PDF
                </span>
                <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success" style="font-size:.65rem;">
                    <i class="fa fa-music me-1"></i>Audio
                </span>
                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary" style="font-size:.65rem;">
                    <i class="fa fa-video-camera me-1"></i>Video
                </span>
                <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning" style="font-size:.65rem;">
                    <i class="fa fa-users me-1"></i>Aportaciones
                </span>
                <span class="badge rounded-pill bg-light text-secondary border" style="font-size:.65rem;">
                    <i class="fa fa-folder-open me-1"></i>Sub-registros
                </span>
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
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput    = document.getElementById('searchCategory');
        const clearButton    = document.getElementById('clearSearch');
        const expandAllBtn   = document.getElementById('expandAll');
        const collapseAllBtn = document.getElementById('collapseAll');
        const noResults      = document.getElementById('noResults');

        function allCollapses() {
            return Array.from(document.querySelectorAll('[data-tree-children]'));
        }

        function setToggleIcon(btn, expanded) {
            const icon = btn ? btn.querySelector('.toggle-icon') : null;
            if (!icon) return;
            icon.classList.toggle('fa-chevron-right', !expanded);
            icon.classList.toggle('fa-chevron-down',   expanded);
        }

        function expandCollapse(list, expand) {
            list.classList.toggle('show', expand);
            const btn = document.querySelector(`[data-bs-target="#${list.id}"]`);
            setToggleIcon(btn, expand);
            if (btn) btn.setAttribute('aria-expanded', expand);
        }

        expandAllBtn.addEventListener('click', () =>
            allCollapses().forEach(l => expandCollapse(l, true)));
        collapseAllBtn.addEventListener('click', () =>
            allCollapses().forEach(l => expandCollapse(l, false)));

        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (btn) {
            const target = document.querySelector(btn.getAttribute('data-bs-target'));
            if (!target) return;
            target.addEventListener('show.bs.collapse', () => setToggleIcon(btn, true));
            target.addEventListener('hide.bs.collapse', () => setToggleIcon(btn, false));
        });

        // ── Resaltado de coincidencias ────────────────────────────
        function highlight(el, term) {
            if (!el) return;
            if (!el.dataset.origHtml) el.dataset.origHtml = el.innerHTML;
            const esc = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            el.innerHTML = el.dataset.origHtml.replace(
                new RegExp(`(${esc})`, 'gi'),
                '<mark style="background:#fff176;padding:0 2px;border-radius:3px;font-weight:inherit;">$1</mark>'
            );
        }

        function clearHighlights() {
            document.querySelectorAll('[data-orig-html]').forEach(el => {
                el.innerHTML = el.dataset.origHtml;
                delete el.dataset.origHtml;
            });
        }

        function showAll() {
            clearHighlights();
            Array.from(document.querySelectorAll('[data-category-item]')).forEach(node => {
                const row = node.closest('.category-node');
                if (row) row.classList.remove('d-none');
            });
            allCollapses().forEach(l => expandCollapse(l, false));
            noResults.classList.add('d-none');
        }

        function filterCategories(term) {
            const search = term.trim().toLowerCase();
            if (!search) { showAll(); return; }

            clearHighlights();
            allCollapses().forEach(l => l.classList.remove('show'));
            let matches = 0;

            Array.from(document.querySelectorAll('[data-category-item]')).forEach(node => {
                const row = node.closest('.category-node');
                if (!row) return;
                const isMatch = (node.dataset.searchable || '').includes(search);
                row.classList.toggle('d-none', !isMatch);
                if (isMatch) {
                    matches++;
                    // Resaltar en nombre y descripción
                    highlight(node.querySelector('.fw-semibold'), term.trim());
                    highlight(node.querySelector('small.text-muted'), term.trim());

                    // Mostrar ancestros
                    let ancestor = row.parentElement?.closest('.category-node');
                    while (ancestor) {
                        ancestor.classList.remove('d-none');
                        const childList = ancestor.querySelector('[data-tree-children]');
                        if (childList) expandCollapse(childList, true);
                        ancestor = ancestor.parentElement?.closest('.category-node');
                    }
                }
            });

            noResults.classList.toggle('d-none', matches > 0);
        }

        let debounce = null;
        searchInput.addEventListener('input', function (e) {
            clearTimeout(debounce);
            debounce = setTimeout(() => filterCategories(e.target.value), 200);
        });

        clearButton.addEventListener('click', function () {
            searchInput.value = '';
            showAll();
            searchInput.focus();
        });
    });
</script>
@endsection
