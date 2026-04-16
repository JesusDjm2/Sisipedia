<li class="mb-2 category-node">
    <div class="list-group-item border rounded-3" data-category-item
        data-searchable="{{ strtolower($category->name . ' ' . ($category->description ?? '')) }}">
        <div class="d-flex flex-column flex-md-row gap-3 align-items-start align-items-md-center justify-content-between">
            <div class="d-flex gap-3 align-items-start align-items-md-center flex-grow-1">
                @if ($category->image)
                    <img src="{{ asset($category->image) }}" alt="{{ $category->name }}"
                        class="rounded-circle flex-shrink-0" width="42" height="42" loading="lazy" decoding="async">
                @else
                    <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:42px;height:42px;">
                        <i class="fa fa-folder"></i>
                    </div>
                @endif

                <div class="w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="fw-semibold">{{ $category->name }}</span>
                        @if ($category->children->count() > 0)
                            <span class="badge text-bg-light border">
                                <i class="fa fa-folder-open me-1"></i>{{ $category->children->count() }}
                            </span>
                        @endif
                    </div>
                    @if ($category->description)
                        <small class="text-muted d-block">{{ Str::limit($category->description, 100) }}</small>
                    @endif
                    <small class="text-muted">
                        <i class="fa fa-sort-numeric-down me-1"></i>Orden: {{ $category->order }}
                    </small>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('sisipedia.categories.show', $category) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-eye me-1"></i>Ver
                </a>
                @if ($category->children->count() > 0)
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#subcategories-{{ $category->id }}" aria-expanded="true">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>

    @php
        $publicChildren = $category->children->where('is_active', true);
    @endphp

    @if ($publicChildren->count() > 0)
        <ul id="subcategories-{{ $category->id }}" class="list-unstyled ms-3 ms-md-4 mt-2 collapse show" data-tree-children>
            @foreach ($publicChildren as $child)
                @include('sisichakuna.tree', [
                    'category' => $child,
                    'level' => $level + 1,
                ])
            @endforeach
        </ul>
    @endif
</li>
