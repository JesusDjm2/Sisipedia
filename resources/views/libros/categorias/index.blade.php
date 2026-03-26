@extends('layouts.app')
@section('titulo', 'Categorias')
@section('contenido')
    <div class="row">
        <div class="col-md-12 mt-3">
            <h3>Categorias
                <a href="{{ route('categorias.create') }}" class="btn btn-info btn-sm float-right">Crear Nueva Categoria</a>
            </h3>
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
        <div class="col-lg-12 mt-3 table-responsive">
            <input type="text" id="search" class="form-control form-control-sm" placeholder="Buscar categoría...">
        </div>
        <div class="col-lg-12">
            <table class="table table-hover mt-4">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Sección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="categoryTable">
                    @php $counter = 1; @endphp
                    @foreach ($categorias as $categoria)
                        <tr>
                            <td>{{ $counter }}</td>
                            <td>
                                <a href="{{ route('detallesCat', $categoria->id) }}">{{ $categoria->nombre }}</a>
                                <span style="font-size: 0.8em">
                                    → {{ $categoria->libros_count }} {{ Str::plural('libro', $categoria->libros_count) }}
                                </span>
                            </td>
                            <td>{{ $categoria->seccion->nombre }}

                            </td>
                            <td>
                                <a href="{{ route('categorias.show', $categoria->url) }}" class="btn btn-sm btn-info"><i
                                        class="fa fa-eye"></i></a>
                                <a href="{{ route('categorias.edit', $categoria->id) }}" class="btn btn-sm btn-warning"><i
                                        class="fa fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="confirmDelete('{{ route('categorias.destroy', $categoria->id) }}')"><i
                                        class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        @php $counter++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar esta categoria?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function confirmDelete(url) {
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = url;
            $('#deleteModal').modal('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const categoryTable = document.getElementById('categoryTable');
            const rows = categoryTable.getElementsByTagName('tr');

            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase();
                Array.from(rows).forEach(row => {
                    const columns = row.getElementsByTagName('td');
                    const name = columns[1].textContent.toLowerCase();
                    const section = columns[2].textContent.toLowerCase();
                    if (name.includes(query) || section.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endsection
