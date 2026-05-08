{{--
    Barra de navegación Sisipedia (panel administrativo).
    $active: 'home' | 'categories' | 'aportaciones'
--}}
@php
    $active = $active ?? 'categories';
@endphp
<div class="card border-0 shadow-sm mb-4 sisipedia-admin-nav">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:.75rem;">
            <div class="d-flex flex-wrap align-items-center" style="gap:.35rem;">
                <span class="text-muted small text-uppercase font-weight-bold mr-2 d-none d-sm-inline">Sisipedia</span>
                <a href="{{ route('sisipedia.categories.index') }}"
                    class="btn btn-sm {{ $active === 'categories' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="fa fa-sitemap mr-1"></i>Registros
                </a>
                <a href="{{ route('sisipedia.aportaciones.index') }}"
                    class="btn btn-sm {{ $active === 'aportaciones' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="fa fa-users mr-1"></i>Aportaciones
                </a>
            </div>
            <a href="{{ route('public.categoria.sisi') }}" target="_blank" rel="noopener"
                class="btn btn-sm btn-outline-info">
                <i class="fa fa-external-link mr-1"></i>Vista pública
            </a>
        </div>
    </div>
</div>
