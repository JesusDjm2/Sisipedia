@extends('layouts.app')
@section('titulo', 'Secciones')
@section('contenido')
    <div class="row">
        <div class="col-md-12">
            <h3>Secciones
                <a href="{{ route('secciones.create') }}" class="btn btn-info btn-sm float-right">Crear Nueva Sección</a>
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

            <table class="table table-hover mt-4">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Categorias</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @foreach ($secciones as $seccion)
                        <tr>
                            <td>{{ $counter }}</td>
                            <td class="font-weight-bold">{{ $seccion->nombre }}</td>
                            <td>
                                @if ($seccion->categorias->isEmpty())
                                    <ul>
                                        <i>Sin categorías</i>
                                    </ul>
                                @else
                                    <ul>
                                        @foreach ($seccion->categorias as $categoria)
                                            <li>
                                                <a href="{{ route('detallesCat', $categoria->id) }}">
                                                    {{ $categoria->nombre }}
                                                </a>
                                                <span style="font-size: 0.8em">({{ $categoria->libros_count }}
                                                    libros)</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('secciones.show', $seccion->id) }}" class="btn btn-sm btn-info"><i
                                        class="fa fa-eye"></i></a>
                                <a href="{{ route('secciones.edit', $seccion->id) }}" class="btn btn-sm btn-warning"><i
                                        class="fa fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="confirmDelete('{{ route('secciones.destroy', $seccion->id) }}')"><i
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
                    ¿Estás seguro de que deseas eliminar esta sección?
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
            // Additional script for handling other functionalities if needed
        });
    </script>
@endsection
