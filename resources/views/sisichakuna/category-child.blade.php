<div class="category-item-public mb-2" data-name="{{ strtolower($category->display_name) }}">
    <div class="accordion" id="accordion-child-{{ $category->id }}">
        <div class="accordion-item border-0">
            <div class="accordion-header">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <button class="btn w-100 text-start p-3 d-flex align-items-start justify-content-between"
                            type="button" data-bs-toggle="collapse" data-bs-target="#collapse-child-{{ $category->id }}"
                            aria-expanded="{{ $level == 1 ? 'true' : 'false' }}"
                            aria-controls="collapse-child-{{ $category->id }}">
                            <div class="d-flex align-items-start flex-grow-1">
                                <!-- Icono pequeño para subcategorías -->
                                <div class="flex-shrink-0">
                                    @if ($category->image)
                                        <img src="{{ asset($category->image) }}" alt="{{ $category->display_name }}"
                                            class="rounded-circle"
                                            style="width: 35px; height: 35px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 35px; height: 35px;">
                                            <i class="fa fa-folder fa-lg text-secondary"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Contenido -->
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">
                                        <span class="badge bg-secondary me-2">{{ $category->numbering }}</span>
                                        {{ $category->display_name }}
                                    </h6>
                                    @if ($category->description)
                                        <p class="text-muted small mb-1">{{ Str::limit($category->description, 100) }}
                                        </p>
                                    @endif
                                    <div class="d-flex gap-3 small text-muted">
                                        <span>
                                            <i class="fa fa-tag me-1"></i>
                                            {{ $category->slug }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @if ($category->children->count())
                                <div class="ms-3">
                                    <i class="fa fa-chevron-down accordion-icon"></i>
                                </div>
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            <!-- Subcategorías anidadas con acordeón -->
            @if ($category->children->count())
                <div id="collapse-child-{{ $category->id }}"
                    class="accordion-collapse collapse {{ $level == 1 ? 'show' : '' }}"
                    data-bs-parent="#accordion-child-{{ $category->id }}">
                    <div class="accordion-body p-3 ps-5 bg-light">
                        @foreach ($category->children as $child)
                            @include('sisichakuna.category-child', [
                                'category' => $child,
                                'level' => $level + 1,
                            ])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
