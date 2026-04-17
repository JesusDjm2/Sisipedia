{{-- Formulario de aporte general (sin registro / categoría) — portada --}}
<section id="home-aporte-standalone" class="mt-5 pt-4 border-top">
    <div class="row justify-content-center mb-4">
        <div class="col-lg-12">
            <h2 class="titulosDos mb-2">Nuevo aporte</h2>
            <p class="text-muted small mb-0">
                Comparte materiales sin elegir un registro concreto. El rol <strong>Equipo Puklla</strong> identifica al
                personal institucional; otros roles siguen igual que en los registros Sisipedia.
                Tu envío quedará <strong>pendiente de revisión</strong> hasta que un administrador lo apruebe.
            </p>
        </div>
    </div>

    @if (session('aporte_success'))
        <div class="row justify-content-center mb-4">
            <div class="col-lg-12">
                <div class="alert alert-success border-0 shadow-sm rounded-3">
                    <i class="fa fa-check-circle me-2"></i>{{ session('aporte_success') }}
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row justify-content-center mb-4">
            <div class="col-lg-12">
                <div class="alert alert-danger border-0 shadow-sm rounded-3">
                    <ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            </div>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h3 class="h5 mb-0"><i class="fa fa-plus-circle me-2 text-primary"></i>Agregar aportación</h3>
                        <button type="button" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm rounded-pill"
                            id="btnHomeAbrirRoles" onclick="homeMostrarRoles()"
                            style="background:linear-gradient(135deg,#0d6efd,#6610f2); border:none;">
                            Comenzar
                        </button>
                    </div>
                </div>

                <div id="homeRolSelector" class="px-4 pt-3 pb-2 d-none">
                    <p class="fw-semibold mb-3 text-center">¿Cómo deseas aportar?</p>
                    <div class="row row-cols-2 row-cols-lg-4 g-3 justify-content-center mb-3 home-rol-grid">
                        <div class="col">
                            <div class="card border-2 rounded-4 text-center p-3 h-100 home-rol-card"
                                style="cursor:pointer;border-color:#6f42c1!important;"
                                onclick="homeSeleccionarRol('Equipo Puklla')">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mb-2"
                                    style="width:52px;height:52px;background:#6f42c1;">
                                    <i class="fa fa-users fa-lg"></i>
                                </span>
                                <div class="fw-bold small">Equipo Puklla</div>
                                <small class="text-muted" style="font-size:.7rem;">Institucional</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card border-2 rounded-4 text-center p-3 h-100 home-rol-card"
                                style="cursor:pointer;border-color:#0d6efd!important;"
                                onclick="homeSeleccionarRol('Docente')">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white mb-2"
                                    style="width:52px;height:52px;">
                                    <i class="fa fa-chalkboard-teacher"></i>
                                </span>
                                <div class="fw-bold small">Docente</div>
                                <small class="text-muted" style="font-size:.7rem;">Educador</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card border-2 rounded-4 text-center p-3 h-100 home-rol-card"
                                style="cursor:pointer;border-color:#198754!important;"
                                onclick="homeSeleccionarRol('Líder')">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white mb-2"
                                    style="width:52px;height:52px;">
                                    <i class="fa fa-star"></i>
                                </span>
                                <div class="fw-bold small">Líder</div>
                                <small class="text-muted" style="font-size:.7rem;">Comunidad</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card border-2 rounded-4 text-center p-3 h-100 home-rol-card"
                                style="cursor:pointer;border-color:#fd7e14!important;"
                                onclick="homeSeleccionarRol('Niño/Estudiante')">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mb-2"
                                    style="width:52px;height:52px;background:#fd7e14;">
                                    <i class="fa fa-child"></i>
                                </span>
                                <div class="fw-bold small">Niño / Estudiante</div>
                                <small class="text-muted" style="font-size:.7rem;">Estudiante</small>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="homeCancelarAportacion()">
                            <i class="fa fa-times me-1"></i>Cancelar
                        </button>
                    </div>
                </div>

                <div id="homeFormAportacionWrap" class="px-4 pb-4 pt-2 d-none">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge fs-6 px-3 py-2 text-white" id="homeRolBadge" style="background:#0d6efd;">
                            <i class="fa fa-user me-1"></i><span id="homeRolTexto"></span>
                        </span>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="homeVolverARoles()">
                            <i class="fa fa-arrow-left me-1"></i>Cambiar rol
                        </button>
                    </div>

                    <form action="{{ route('sisipedia.aportaciones.general.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="rol_nombre" id="homeInputRolNombre" value="{{ old('rol_nombre') }}">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre en lengua original <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nombre_ol" required
                                    class="form-control @error('nombre_ol') is-invalid @enderror"
                                    value="{{ old('nombre_ol') }}">
                                @error('nombre_ol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Institución</label>
                                <input type="text" name="institucion" class="form-control @error('institucion') is-invalid @enderror"
                                    value="{{ old('institucion') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Ubicación</label>
                                <input type="text" name="ubicacion" class="form-control @error('ubicacion') is-invalid @enderror"
                                    value="{{ old('ubicacion') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Detalle</label>
                                <textarea name="detalle" rows="3"
                                    class="form-control @error('detalle') is-invalid @enderror">{{ old('detalle') }}</textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="fa fa-file-pdf text-danger me-1"></i>PDF</label>
                                <input type="file" name="pdf" accept=".pdf" class="form-control @error('pdf') is-invalid @enderror">
                                @error('pdf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="fa fa-file-word text-primary me-1"></i>Word</label>
                                <input type="file" name="doc" accept=".doc,.docx" class="form-control @error('doc') is-invalid @enderror">
                                @error('doc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="fa fa-music text-success me-1"></i>Audio</label>
                                <input type="file" name="audio" accept=".mp3,.wav,.ogg" class="form-control @error('audio') is-invalid @enderror">
                                @error('audio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="fa fa-video text-warning me-1"></i>Video</label>
                                <input type="file" name="video" accept=".mp4,.webm,.mov" class="form-control @error('video') is-invalid @enderror">
                                @error('video')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane me-1"></i>Enviar aporte</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="homeCancelarAportacion()">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    const homeRolColors = {
        'Equipo Puklla': '#6f42c1',
        'Docente': '#0d6efd',
        'Líder': '#198754',
        'Niño/Estudiante': '#fd7e14',
    };

    window.homeMostrarRoles = function () {
        document.getElementById('homeRolSelector').classList.remove('d-none');
        document.getElementById('homeFormAportacionWrap').classList.add('d-none');
        document.getElementById('btnHomeAbrirRoles').classList.add('d-none');
    };

    window.homeSeleccionarRol = function (rol) {
        document.getElementById('homeInputRolNombre').value = rol;
        document.getElementById('homeRolTexto').textContent = rol;
        document.getElementById('homeRolBadge').style.background = homeRolColors[rol] ?? '#6c757d';
        document.getElementById('homeRolSelector').classList.add('d-none');
        document.getElementById('homeFormAportacionWrap').classList.remove('d-none');
    };

    window.homeVolverARoles = function () {
        document.getElementById('homeFormAportacionWrap').classList.add('d-none');
        document.getElementById('homeRolSelector').classList.remove('d-none');
    };

    window.homeCancelarAportacion = function () {
        document.getElementById('homeRolSelector').classList.add('d-none');
        document.getElementById('homeFormAportacionWrap').classList.add('d-none');
        const b = document.getElementById('btnHomeAbrirRoles');
        if (b) b.classList.remove('d-none');
    };

    @if ($errors->any() && old('rol_nombre'))
        document.addEventListener('DOMContentLoaded', function () {
            homeMostrarRoles();
            homeSeleccionarRol(@json(old('rol_nombre')));
        });
    @endif
})();
</script>
