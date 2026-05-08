@extends('layouts.admin')
@section('content')
    <div class="container">
        <h1>Categorías</h1>

        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary mb-3">Nueva Categoría</a>
        <a href="{{ route('admin.categories.tree') }}" class="btn btn-info mb-3">Ver Árbol</a>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Ruta</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->display_name }}</td>
                        <td>{{ $category->path }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $category->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('admin.categories.edit', $category) }}"
                                class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
