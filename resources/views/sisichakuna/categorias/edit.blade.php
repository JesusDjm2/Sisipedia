@extends('layouts.app')
@section('titulo', 'Editar Categoría')
@section('contenido')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Editar Categoría: {{ $category->name }}</h3>
                        <div class="card-tools">
                            <a href="{{ route('sisipedia.categories.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('sisipedia.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $category->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="slug">Slug (URL amigable)</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                    id="slug" name="slug" value="{{ old('slug', $category->slug) }}"
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
                                            data-numbering="{{ $parent->numbering }}"
                                            {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->numbering }} - {{ $parent->name }}
                                        </option>
                                        @if ($parent->children->count())
                                            @foreach ($parent->children as $child)
                                                @if($child->id != $category->id)
                                                    <option value="{{ $child->id }}"
                                                        data-numbering="{{ $child->numbering }}"
                                                        {{ old('parent_id', $category->parent_id) == $child->id ? 'selected' : '' }}>
                                                        {{ $child->numbering }} - {{ $child->name }}
                                                    </option>
                                                    @if ($child->children->count())
                                                        @foreach ($child->children as $grandchild)
                                                            @if($grandchild->id != $category->id)
                                                                <option value="{{ $grandchild->id }}"
                                                                    data-numbering="{{ $grandchild->numbering }}"
                                                                    {{ old('parent_id', $category->parent_id) == $grandchild->id ? 'selected' : '' }}>
                                                                    {{ $grandchild->numbering }} - {{ $grandchild->name }}
                                                                </option>
                                                                @if ($grandchild->children->count())
                                                                    @foreach ($grandchild->children as $greatGrandchild)
                                                                        @if($greatGrandchild->id != $category->id)
                                                                            <option value="{{ $greatGrandchild->id }}"
                                                                                data-numbering="{{ $greatGrandchild->numbering }}"
                                                                                {{ old('parent_id', $category->parent_id) == $greatGrandchild->id ? 'selected' : '' }}>
                                                                                {{ $greatGrandchild->numbering }} - {{ $greatGrandchild->name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Selecciona la categoría padre para mover esta categoría
                                </small>
                            </div>

                            <!-- Información de categoría actual -->
                            <div class="form-group mb-3">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <strong>Información actual:</strong><br>
                                    Numeración: <strong>{{ $category->numbering }}</strong><br>
                                    Ruta: <strong>{{ $category->path }}</strong>
                                </div>
                            </div>

                            <!-- Vista previa de numeración después de cambio -->
                            <div class="form-group mb-3" id="previewNumbering" style="display: none;">
                                <div class="alert alert-warning">
                                    <i class="fa fa-sort-numeric-up-alt me-2"></i>
                                    Después de guardar, esta categoría tendrá la numeración: <strong id="previewNumberingValue"></strong>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description">Descripción</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="4">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="image">Imagen</label>
                                @if($category->image)
                                    <div class="mb-2">
                                        <img src="{{ asset($category->image) }}"
                                             alt="{{ $category->name }}"
                                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image" value="1">
                                            <label class="form-check-label text-danger" for="remove_image">
                                                <i class="fa fa-trash"></i> Eliminar imagen actual
                                            </label>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    id="image" name="image" accept="image/*">
                                <small class="form-text text-muted">Formatos: JPG, PNG, GIF, WEBP. Máximo 2MB.</small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- PDF --}}
                            <div class="form-group mb-3">
                                <label for="pdf"><i class="fa fa-file-pdf text-danger me-1"></i> PDF</label>
                                @if($category->pdf)
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <a href="{{ \App\Services\GoogleDriveService::getUrl($category->pdf) }}"
                                           target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-external-link-alt me-1"></i> Ver PDF actual
                                        </a>
                                        <div class="form-check mb-0">
                                            <input type="checkbox" class="form-check-input" id="remove_pdf" name="remove_pdf" value="1">
                                            <label class="form-check-label text-danger" for="remove_pdf">
                                                <i class="fa fa-trash"></i> Eliminar
                                            </label>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('pdf') is-invalid @enderror"
                                    id="pdf" name="pdf" accept=".pdf">
                                <small class="form-text text-muted">Formato: PDF. Máximo 20MB. Se sube a Google Drive.</small>
                                @error('pdf')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Audio --}}
                            <div class="form-group mb-3">
                                <label for="audio"><i class="fa fa-music text-primary me-1"></i> Audio</label>
                                @if($category->audio)
                                    <div class="mb-2">
                                        <audio controls class="w-100" style="max-width:400px;">
                                            <source src="{{ \App\Services\GoogleDriveService::getEmbedUrl($category->audio) }}">
                                        </audio>
                                        <div class="form-check mt-1">
                                            <input type="checkbox" class="form-check-input" id="remove_audio" name="remove_audio" value="1">
                                            <label class="form-check-label text-danger" for="remove_audio">
                                                <i class="fa fa-trash"></i> Eliminar audio actual
                                            </label>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('audio') is-invalid @enderror"
                                    id="audio" name="audio" accept=".mp3,.wav,.ogg">
                                <small class="form-text text-muted">Formatos: MP3, WAV, OGG. Máximo 50MB. Se sube a Google Drive.</small>
                                @error('audio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Video --}}
                            <div class="form-group mb-3">
                                <label for="video"><i class="fa fa-video text-success me-1"></i> Video</label>
                                @if($category->video)
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <a href="{{ \App\Services\GoogleDriveService::getUrl($category->video) }}"
                                           target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="fa fa-external-link-alt me-1"></i> Ver video actual
                                        </a>
                                        <div class="form-check mb-0">
                                            <input type="checkbox" class="form-check-input" id="remove_video" name="remove_video" value="1">
                                            <label class="form-check-label text-danger" for="remove_video">
                                                <i class="fa fa-trash"></i> Eliminar
                                            </label>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('video') is-invalid @enderror"
                                    id="video" name="video" accept=".mp4,.webm,.mov">
                                <small class="form-text text-muted">Formatos: MP4, WEBM, MOV. Máximo 200MB. Se sube a Google Drive.</small>
                                @error('video')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="order">Orden <small class="text-info">Ejemplos: hijo (order = 1) → será .1 | (order = 2) → será .2 | (order = 3) → será .3</small></label>
                                <input type="number" class="form-control @error('order') is-invalid @enderror"
                                    id="order" name="order" value="{{ old('order', $category->order) }}"
                                    placeholder="Se asigna automáticamente si se deja vacío">
                                <small class="form-text text-muted">
                                    Define el orden dentro de la misma categoría padre. Números más bajos aparecerán primero. 
                                    Cambiar el orden afectará la numeración.
                                </small>
                                @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Activa
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar
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
            const removeImageCheckbox = document.getElementById('remove_image');
            const imageInput = document.getElementById('image');

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
            function getNextNumber(parentId, currentOrder = null) {
                if (!parentId) {
                    // Es categoría raíz - contar raíces existentes
                    const rootOptions = Array.from(parentSelect.options)
                        .filter(opt => opt.value !== '' && !opt.getAttribute('data-numbering'));
                    return rootOptions.length + 1;
                } else {
                    // Es subcategoría - contar hijos del padre seleccionado
                    const selectedOption = parentSelect.options[parentSelect.selectedIndex];
                    const parentNumbering = selectedOption.getAttribute('data-numbering');
                    
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
                const currentNumbering = '{{ $category->numbering }}';
                
                if (!parentSelect.value) {
                    // Es categoría raíz
                    const nextNumber = getNextNumber(null);
                    const newNumbering = nextNumber.toString();
                    if (newNumbering !== currentNumbering) {
                        previewNumberingValue.textContent = newNumbering;
                        previewNumbering.style.display = 'block';
                    } else {
                        previewNumbering.style.display = 'none';
                    }
                } else if (parentNumbering) {
                    // Es subcategoría
                    const nextNumber = getNextNumber(parentSelect.value);
                    const newNumbering = parentNumbering + '.' + nextNumber;
                    if (newNumbering !== currentNumbering) {
                        previewNumberingValue.textContent = newNumbering;
                        previewNumbering.style.display = 'block';
                    } else {
                        previewNumbering.style.display = 'none';
                    }
                } else {
                    previewNumbering.style.display = 'none';
                }
            }

            parentSelect.addEventListener('change', calculatePreviewNumbering);
            
            // Calcular inicialmente si hay un valor seleccionado
            if (parentSelect.value) {
                calculatePreviewNumbering();
            }

            // Manejar eliminación de imagen
            if (removeImageCheckbox) {
                removeImageCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        imageInput.disabled = false;
                        imageInput.required = false;
                    } else {
                        imageInput.disabled = false;
                    }
                });
            }
        });
    </script>
@endsection