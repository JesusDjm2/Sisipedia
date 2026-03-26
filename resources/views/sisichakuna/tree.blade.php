@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Árbol de Categorías</h1>

        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mb-3">Volver</a>

        <div class="tree">
            @foreach ($tree as $category)
                <li>
                    <div class="category-item">
                        <strong>{{ $category->name }}</strong>
                        @if ($category->description)
                            <p class="small text-muted mb-0">{{ Str::limit($category->description, 50) }}</p>
                        @endif
                        <div class="mt-2">
                            <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('admin.categories.edit', $category) }}"
                                class="btn btn-sm btn-warning">Editar</a>
                        </div>
                    </div>

                    @if ($category->children->count() > 0)
                        <ul class="children">
                            @foreach ($category->children as $child)
                                @include('admin.categories.partials.category-node', ['category' => $child])
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .tree ul {
            list-style-type: none;
            padding-left: 20px;
        }

        .tree li {
            margin: 10px 0;
            position: relative;
        }

        .tree .category-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            display: inline-block;
            min-width: 250px;
        }

        .tree .children {
            margin-left: 30px;
            border-left: 2px dashed #dee2e6;
            padding-left: 20px;
        }
    </style>
@endpush
