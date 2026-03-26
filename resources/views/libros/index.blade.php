@extends('layouts.app')
@section('titulo', 'Libros')
@section('contenido')
    <div class="row">
        <div class="col-md-12">
            <h3>Libros
                <a href="{{ route('libros.create') }}" class="btn btn-info btn-sm float-right">Agregar nuevo Libro</a>
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
            <input type="text" id="search" class="form-control form-control-sm mt-3"
                placeholder="Buscar texto/autor...">
            <div class="table-responsive">
                <table class="table table-hover mt-4">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Nombre de texto</th>
                            <th>Sección<small>&nbsp;
                                    <i class="fa fa-angle-right font-weight-bold"></i>
                                    <i class="fa fa-angle-right font-weight-bold"></i>
                                </small>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="librosTable">
                        @php $counter = 1; @endphp
                        @foreach ($libros as $libro)
                            <tr class="libro-item">
                                <td>{{ $counter }}</td>
                                <td class="nombre">{{ $libro->nombre }}<br>

                                    <ul>
                                        <li style="font-size: 14px">
                                            <strong class="autor">{{ $libro->autor }}
                                        </li>
                                    </ul>
                                </td>
                                <td>
                                    <ul>
                                        @foreach ($libro->categorias as $categoria)
                                            <li>
                                                <a href="{{ route('secciones.show', $categoria->seccion->id) }}">{{ $categoria->seccion->nombre }}
                                                </a>
                                                <small>&nbsp;
                                                    <i class="fa fa-angle-right font-weight-bold text-info"></i>
                                                    <i class="fa fa-angle-right font-weight-bold text-info"></i>
                                                </small> &nbsp;
                                                <a
                                                    href="{{ route('detallesCat', $categoria->id) }}">{{ $categoria->nombre }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <a href="https://drive.google.com/file/d/{{ $libro->identificador }}/view"
                                        target="_blank" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('libros.edit', $libro->id) }}" class="btn btn-sm btn-warning"><i
                                            class="fa fa-edit"></i></a>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete"
                                        data-url="{{ route('libros.destroy', $libro->id) }}"
                                        data-nombre="{{ $libro->nombre }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @php $counter++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                <div class="modal-body text-center">
                    <p>¿Estás seguro de que deseas eliminar este Libro?
                        <span>
                            <p class="text-primary">'<strong id="libroNombre"></strong>'</p>
                        </span>
                    </p>
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
        function confirmDelete(url, nombreLibro) {
            $('#deleteModal').modal('show');
            document.getElementById('deleteForm').action = url;
            document.getElementById('libroNombre').textContent = nombreLibro;
        }
    </script>
    <script>
        // Mostrar modal con datos correctos
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                const button = e.target.closest('.btn-delete');
                const url = button.getAttribute('data-url');
                const nombre = button.getAttribute('data-nombre');
                confirmDelete(url, nombre);
            }
        });

        // Filtro en tiempo real
        document.getElementById('search').addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('#librosTable .libro-item');

            rows.forEach(row => {
                const nombre = row.querySelector('.nombre').textContent.toLowerCase();
                const autor = row.querySelector('.autor').textContent.toLowerCase();
                const match = nombre.includes(term) || autor.includes(term);
                row.style.display = match ? '' : 'none';
            });
        });

        // Confirm Delete Function (ya existente)
        function confirmDelete(url, nombreLibro) {
            $('#deleteModal').modal('show');
            document.getElementById('deleteForm').action = url;
            document.getElementById('libroNombre').textContent = nombreLibro;
        }
    </script>

@endsection
