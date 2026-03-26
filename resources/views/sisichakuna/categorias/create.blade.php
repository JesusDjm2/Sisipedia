@extends('layouts.app')
@section('titulo', 'Nueva Categoría')
@section('contenido')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Crear Nueva Categoría</h3>

                        <a href="{{ route('sisipedia.categories.index') }}" class="btn btn-danger btn-sm">
                            <i class="fa fa-arrow-left"></i> Volver
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
                                        <option value="{{ $parent->id }}" data-numbering="{{ $parent->numbering }}"
                                            {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->numbering }} - {{ $parent->name }}
                                        </option>
                                        @if ($parent->children->count())
                                            @foreach ($parent->children as $child)
                                                <option value="{{ $child->id }}"
                                                    data-numbering="{{ $child->numbering }}"
                                                    {{ old('parent_id') == $child->id ? 'selected' : '' }}>
                                                    {{ $child->numbering }} - {{ $child->name }}
                                                </option>
                                                @if ($child->children->count())
                                                    @foreach ($child->children as $grandchild)
                                                        <option value="{{ $grandchild->id }}"
                                                            data-numbering="{{ $grandchild->numbering }}"
                                                            {{ old('parent_id') == $grandchild->id ? 'selected' : '' }}>
                                                            {{ $grandchild->numbering }} - {{ $grandchild->name }}
                                                        </option>
                                                        @if ($grandchild->children->count())
                                                            @foreach ($grandchild->children as $greatGrandchild)
                                                                <option value="{{ $greatGrandchild->id }}"
                                                                    data-numbering="{{ $greatGrandchild->numbering }}"
                                                                    {{ old('parent_id') == $greatGrandchild->id ? 'selected' : '' }}>
                                                                    {{ $greatGrandchild->numbering }} -
                                                                    {{ $greatGrandchild->name }}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Selecciona la categoría padre para crear una
                                    subcategoría
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
                                <small class="form-text text-muted">
                                    Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.
                                </small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="order">Orden <small class="text-info">Ejemplos: hijo (order = 1) → será 1.1 |
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

            // Función para obtener el próximo número de orden
            function getNextNumber(parentId) {
                if (!parentId) {
                    // Es categoría raíz
                    const rootOptions = Array.from(parentSelect.options)
                        .filter(opt => opt.value !== '' && !opt.getAttribute('data-numbering'));
                    return rootOptions.length + 1;
                } else {
                    // Es subcategoría - contar cuántos hijos tiene este padre específico
                    const selectedOption = parentSelect.options[parentSelect.selectedIndex];
                    const parentNumbering = selectedOption.getAttribute('data-numbering');

                    // Contar opciones que tienen este padre como prefijo exacto (sin contar el padre mismo)
                    const siblings = Array.from(parentSelect.options)
                        .filter(opt => {
                            const numbering = opt.getAttribute('data-numbering');
                            return numbering && numbering.startsWith(parentNumbering + '.');
                        });
                    return siblings.length + 1;
                }
            }

            // Calcular y mostrar la numeración prevista
            function calculatePreviewNumbering() {
                const selectedOption = parentSelect.options[parentSelect.selectedIndex];
                const parentNumbering = selectedOption.getAttribute('data-numbering');

                if (!parentSelect.value) {
                    // Es categoría raíz
                    const nextNumber = getNextNumber(null);
                    previewNumberingValue.textContent = nextNumber.toString();
                    previewNumbering.style.display = 'block';
                } else if (parentNumbering) {
                    // Es subcategoría
                    const nextNumber = getNextNumber(parentSelect.value);
                    previewNumberingValue.textContent = parentNumbering + '.' + nextNumber;
                    previewNumbering.style.display = 'block';
                } else {
                    previewNumbering.style.display = 'none';
                }
            }

            parentSelect.addEventListener('change', calculatePreviewNumbering);

            // Calcular inicialmente si hay un valor seleccionado
            if (parentSelect.value) {
                calculatePreviewNumbering();
            }
        });
    </script>
@endsection
