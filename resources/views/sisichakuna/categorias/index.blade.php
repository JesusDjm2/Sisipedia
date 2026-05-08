@extends('layouts.app')
@section('titulo', 'Categorías')
@section('contenido')
    @include('sisichakuna.partials.sisipedia-admin-nav', ['active' => 'categories'])
    <div class="container-fluid py-0 px-0">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="fa fa-sitemap mr-2 text-primary"></i>Registros Sisipedia
                        </h4>
                        <small class="text-muted">Estructura jerárquica de registros y sub-registros</small>
                    </div>
                    <a href="{{ route('sisipedia.categories.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus mr-1"></i>Nuevo registro
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <!-- Buscador + controles -->
                <div class="mb-4">
                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <div class="input-group flex-grow-1" style="min-width:200px;">
                            <span class="input-group-text bg-white">
                                <i class="fa fa-search text-muted"></i>
                            </span>
                            <input type="text" id="searchCategory" class="form-control form-control-sm"
                                placeholder="Buscar categoría por nombre o slug...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" id="expandAll" title="Expandir todo">
                            <i class="fa fa-expand mr-1"></i> Expandir
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="collapseAll" title="Colapsar todo">
                            <i class="fa fa-compress mr-1"></i> Colapsar
                        </button>
                    </div>
                    <div class="d-flex flex-wrap mt-2" style="gap:.3rem;">
                        <small class="text-muted mr-1">Contiene:</small>
                        <span class="badge badge-danger" style="font-size:.65rem;"><i class="fa fa-file-pdf-o mr-1"></i>PDF</span>
                        <span class="badge badge-success" style="font-size:.65rem;"><i class="fa fa-music mr-1"></i>Audio</span>
                        <span class="badge badge-primary" style="font-size:.65rem;"><i class="fa fa-video-camera mr-1"></i>Video</span>
                        <span class="badge badge-warning text-dark" style="font-size:.65rem;"><i class="fa fa-users mr-1"></i>Aportaciones</span>
                        <span class="badge badge-secondary" style="font-size:.65rem;"><i class="fa fa-folder-open mr-1"></i>Sub-registros</span>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="fa fa-info-circle"></i> La búsqueda filtrará categorías, resaltará coincidencias y mostrará su jerarquía
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
            const searchInput  = document.getElementById('searchCategory');
            const clearButton  = document.getElementById('clearSearch');
            const expandAllBtn = document.getElementById('expandAll');
            const collapseAllBtn = document.getElementById('collapseAll');
            const categoryItems = document.querySelectorAll('.category-item');
            const csrfToken = '{{ csrf_token() }}';

            // ── Expand / Collapse helpers ──────────────────────────────
            function setUlState(ul, expanded) {
                ul.style.display = expanded ? '' : 'none';
                const btn = document.querySelector(`.expand-btn[data-target="${ul.id}"]`);
                if (!btn) return;
                const icon = btn.querySelector('i');
                icon.classList.toggle('fa-chevron-down',  expanded);
                icon.classList.toggle('fa-chevron-right', !expanded);
            }

            expandAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.category-tree ul[id]').forEach(ul => setUlState(ul, true));
            });
            collapseAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.category-tree ul[id]').forEach(ul => setUlState(ul, false));
            });

            // ── Word highlight ─────────────────────────────────────────
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

            function submitPostForm(action, method = null, extraFields = null) {
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

                if (extraFields && typeof extraFields === 'object') {
                    Object.entries(extraFields).forEach(([name, value]) => {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = name;
                        hidden.value = value;
                        form.appendChild(hidden);
                    });
                }

                document.body.appendChild(form);
                form.submit();
            }

            // ── Filter + highlight ─────────────────────────────────────
            function filterCategories(searchTerm) {
                clearHighlights();
                const existingNoResults = document.querySelector('.no-results');

                if (!searchTerm.trim()) {
                    // Restore: show all items, collapse all ULs back to default
                    categoryItems.forEach(item => {
                        item.classList.remove('has-search-match');
                        item.closest('li').style.display = '';
                    });
                    document.querySelectorAll('.category-tree ul[id]').forEach(ul => setUlState(ul, false));
                    if (existingNoResults) existingNoResults.style.display = 'none';
                    return;
                }

                const termLower = searchTerm.toLowerCase().trim();
                let hasMatches = false;

                categoryItems.forEach(item => {
                    const nameEl = item.querySelector('.cat-name-text');
                    const slugEl = item.querySelector('.category-slug');
                    const nameText = (nameEl?.textContent || '').toLowerCase();
                    const slugText = (slugEl?.textContent || '').toLowerCase();
                    const matches  = nameText.includes(termLower) || slugText.includes(termLower);

                    if (matches) {
                        hasMatches = true;
                        item.classList.add('has-search-match');
                        highlight(nameEl, searchTerm.trim());
                        highlight(slugEl, searchTerm.trim());

                        // Show this li and expand all ancestor ULs
                        let li = item.closest('li');
                        while (li) {
                            li.style.display = '';
                            const parentUl = li.parentElement;
                            if (parentUl && parentUl.id) setUlState(parentUl, true);
                            li = parentUl?.closest('li');
                        }
                    } else {
                        item.classList.remove('has-search-match');
                        item.closest('li').style.display = 'none';
                    }
                });

                // No-results message
                if (!hasMatches) {
                    if (!existingNoResults) {
                        const div = document.createElement('div');
                        div.className = 'no-results';
                        div.innerHTML = `<i class="fa fa-search fa-3x mb-3"></i>
                            <p>No se encontraron categorías con "<strong>${searchTerm}</strong>"</p>
                            <small class="text-muted">Intenta con otro término de búsqueda</small>`;
                        document.getElementById('categoryTree').appendChild(div);
                    } else {
                        existingNoResults.innerHTML = `<i class="fa fa-search fa-3x mb-3"></i>
                            <p>No se encontraron categorías con "<strong>${searchTerm}</strong>"</p>
                            <small class="text-muted">Intenta con otro término de búsqueda</small>`;
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
                searchTimeout = setTimeout(() => filterCategories(e.target.value), 300);
            });

            // Botón limpiar búsqueda
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                filterCategories('');
                searchInput.focus();
            });

            // ── Expand / collapse individual ───────────────────────────
            document.querySelectorAll('.expand-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const targetUl = document.getElementById(this.dataset.target);
                    if (targetUl) setUlState(targetUl, targetUl.style.display === 'none');
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
                    const cascade = this.dataset.cascade === '1';
                    const subtreeSize = parseInt(this.dataset.subtreeSize || '1', 10) || 1;

                    const simpleText =
                        `¿Eliminar «${categoryName}»? Esta acción no se puede deshacer.`;
                    const cascadeHtml =
                        `<p class="mb-2">Vas a eliminar <strong>«${categoryName}»</strong> y <strong>toda su ontología</strong> (sub-registros, nietos, etc.).</p>` +
                        `<p class="mb-0"><strong>${subtreeSize}</strong> registro(s) en total, con sus archivos en Drive y aportaciones vinculadas. <span class="text-danger">No se puede deshacer.</span></p>`;

                    const swalOpts = {
                        title: cascade ? 'Eliminar jerarquía completa' : 'Eliminar registro',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: cascade ? 'Sí, eliminar todo' : 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33',
                        reverseButtons: true
                    };
                    if (cascade) {
                        swalOpts.html = cascadeHtml;
                    } else {
                        swalOpts.text = simpleText;
                    }

                    Swal.fire(swalOpts).then((result) => {
                        if (result.isConfirmed) {
                            const extra = cascade ? { cascade_subtree: '1' } : null;
                            submitPostForm(deleteUrl, 'DELETE', extra);
                        }
                    });
                });
            });
        });
    </script>

@endsection
