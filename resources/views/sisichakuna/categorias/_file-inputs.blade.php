{{--
    Partial reutilizado en create y edit.
    Muestra los 4 tipos de input de archivo (múltiples).
    En edit, recibe opcionalmente $existingFiles (colección de CategoryFile).
--}}
@php $existingFiles = $existingFiles ?? collect(); @endphp

<div class="card border rounded-3 mb-3">
    <div class="card-header bg-light py-2">
        <span class="fw-semibold"><i class="fa fa-paperclip me-1"></i> Archivos adjuntos (Google Drive)</span>
        <small class="text-muted ms-2">Puedes subir varios archivos de cada tipo</small>
    </div>
    <div class="card-body">

        {{-- ── Archivos existentes ──────────────────────────────── --}}
        @if($existingFiles->isNotEmpty())
            <div class="mb-3">
                <label class="form-label fw-semibold text-muted small text-uppercase">Archivos actuales</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($existingFiles as $file)
                        @php
                            $color = match($file->tipo) {
                                'pdf'   => 'danger',
                                'doc'   => 'primary',
                                'audio' => 'success',
                                'video' => 'warning',
                                default => 'secondary',
                            };
                            $icon = match($file->tipo) {
                                'pdf'   => 'fa-file-pdf',
                                'doc'   => 'fa-file-word',
                                'audio' => 'fa-music',
                                'video' => 'fa-video',
                                default => 'fa-file',
                            };
                        @endphp
                        <div class="d-flex align-items-center gap-1 border rounded px-2 py-1 bg-white">
                            <i class="fa {{ $icon }} text-{{ $color }}"></i>
                            <span class="small">{{ $file->nombre_display }}</span>
                            <a href="{{ \App\Services\GoogleDriveService::getUrl($file->drive_id) }}"
                               target="_blank" class="btn btn-sm p-0 ms-1 text-{{ $color }}" title="Ver">
                                <i class="fa fa-external-link-alt" style="font-size:.75rem;"></i>
                            </a>
                            <form action="{{ route('sisipedia.categories.files.destroy', [$category, $file]) }}"
                                  method="POST" class="d-inline ms-1"
                                  onsubmit="return confirm('¿Eliminar este archivo?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm p-0 text-danger" title="Eliminar">
                                    <i class="fa fa-times" style="font-size:.75rem;"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <hr class="my-3">
            </div>
        @endif

        {{-- ── Nuevos archivos ─────────────────────────────────── --}}
        <div class="row g-3">
            {{-- PDF --}}
            <div class="col-md-6">
                <label class="form-label">
                    <i class="fa fa-file-pdf text-danger me-1"></i>
                    PDF <small class="text-muted">(uno o varios)</small>
                </label>
                <input type="file" class="form-control @error('pdfs.*') is-invalid @enderror"
                       name="pdfs[]" accept=".pdf" multiple>
                <small class="form-text text-muted">PDF · máx. 20 MB c/u</small>
                @error('pdfs.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Word / Doc --}}
            <div class="col-md-6">
                <label class="form-label">
                    <i class="fa fa-file-word text-primary me-1"></i>
                    Word / Doc <small class="text-muted">(uno o varios)</small>
                </label>
                <input type="file" class="form-control @error('docs.*') is-invalid @enderror"
                       name="docs[]" accept=".doc,.docx" multiple>
                <small class="form-text text-muted">.doc .docx · máx. 20 MB c/u</small>
                @error('docs.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Audio --}}
            <div class="col-md-6">
                <label class="form-label">
                    <i class="fa fa-music text-success me-1"></i>
                    Audio <small class="text-muted">(uno o varios)</small>
                </label>
                <input type="file" class="form-control @error('audios.*') is-invalid @enderror"
                       name="audios[]" accept=".mp3,.wav,.ogg" multiple>
                <small class="form-text text-muted">MP3 WAV OGG · máx. 50 MB c/u</small>
                @error('audios.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Video --}}
            <div class="col-md-6">
                <label class="form-label">
                    <i class="fa fa-video text-warning me-1"></i>
                    Video <small class="text-muted">(uno o varios)</small>
                </label>
                <input type="file" class="form-control @error('videos.*') is-invalid @enderror"
                       name="videos[]" accept=".mp4,.webm,.mov" multiple>
                <small class="form-text text-muted">MP4 WEBM MOV · máx. 200 MB c/u</small>
                @error('videos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>

    </div>
</div>
