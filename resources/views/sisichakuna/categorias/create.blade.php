@extends('layouts.app')
@section('titulo', 'Nueva Categoría')
@section('contenido')
    @include('sisichakuna.partials.sisipedia-admin-nav', ['active' => 'categories'])
    <div class="container-fluid py-4 px-0">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Nuevo registro Sisipedia</h3>

                        <a href="{{ route('sisipedia.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Volver al listado
                        </a>
                    </div>

                    <form action="{{ route('sisipedia.categories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="slug">Slug (URL amigable)</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                    id="slug" name="slug" value="{{ old('slug') }}"
                                    placeholder="Se genera automáticamente si se deja vacío">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="parent_id">Categoría Padre</label>
                                <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id"
                                    name="parent_id">
                                    <option value="">-- Ninguna (Raíz) --</option>
                                    @foreach ($parents as $parent)
                                        <option value="{{ $parent->id }}"
                                            data-depth="{{ $parent->depth }}"
                                            data-numbering="{{ $parent->numbering }}"
                                            {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                            {{ str_repeat('— ', $parent->depth) }}{{ $parent->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Selecciona la categoría padre para crear una subcategoría
                                </small>
                            </div>

                            <!-- Vista previa de numeración -->
                            <div class="form-group mb-3" id="previewNumbering" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fa fa-sort-numeric-up-alt me-2"></i>
                                    Esta categoría tendrá la numeración: <strong id="previewNumberingValue"></strong>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description">Descripción</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="4">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="image">Imagen</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    id="image" name="image" accept="image/*">
                                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, WEBP. Máximo 2MB.</small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Archivos múltiples --}}
                            @include('sisichakuna.categorias._file-inputs')

                            <div class="form-group mb-3">
                                <label for="order">Orden <small class="text-info">Ejemplos: hijo (order = 1) → será 1.1
                                        |
                                        (order = 2) → será 1.2 | (order = 3) → será 1.3</small></label>
                                <input type="number" class="form-control @error('order') is-invalid @enderror"
                                    id="order" name="order" value="{{ old('order') }}"
                                    placeholder="Se asigna automáticamente si se deja vacío">
                                <small class="form-text text-muted">
                                    Define el orden dentro de la misma categoría padre. Números más bajos aparecerán
                                    primero.
                                </small>
                                @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Activa
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="{{ route('sisipedia.categories.index') }}" class="btn btn-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');
            const parentSelect = document.getElementById('parent_id');
            const previewNumbering = document.getElementById('previewNumbering');
            const previewNumberingValue = document.getElementById('previewNumberingValue');

            let slugManuallyEdited = false;

            // Detectar si el usuario modifica el slug manualmente
            slugInput.addEventListener('input', function() {
                slugManuallyEdited = slugInput.value.length > 0;
            });

            // Generar slug desde el nombre
            nameInput.addEventListener('input', function() {
                if (!slugManuallyEdited) {
                    let slug = nameInput.value
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9\s-]/g, '')
                        .trim()
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-');

                    slugInput.value = slug;
                }
            });

            parentSelect.addEventListener('change', function () {
                previewNumbering.style.display = parentSelect.value ? 'block' : 'none';
                if (parentSelect.value) {
                    const depth = parseInt(parentSelect.options[parentSelect.selectedIndex].getAttribute('data-depth') || '0');
                    previewNumberingValue.textContent = '—'.repeat(depth + 1) + ' (nivel ' + (depth + 1) + ')';
                }
            });

            if (parentSelect.value) {
                parentSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection
