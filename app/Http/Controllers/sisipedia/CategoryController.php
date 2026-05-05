<?php

namespace App\Http\Controllers\sisipedia;

use App\Http\Controllers\Controller;
use App\Models\sisipedia\Aportacion;
use App\Models\sisipedia\Category;
use App\Models\sisipedia\CategoryFile;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class CategoryController extends Controller
{
    public function __construct(private GoogleDriveService $drive)
    {
    }

    public function index()
    {
        $categories = Category::with('parent')
            ->orderBy('parent_id')
            ->orderBy('order');
        $tree = Category::with([
            'children.children.children.children',
            'children.files',
            'children.children.files',
            'children.children.children.files',
            'children.children.children.children.files',
            'files',
        ])->whereNull('parent_id')->orderBy('order')->get();

        // Conteo de aportaciones por categoría (una sola consulta)
        $aportCounts = DB::table('aportaciones')
            ->select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        return view('sisichakuna.categorias.index', compact('categories', 'tree', 'aportCounts'));
    }

    public function create()
    {
        $parents = $this->buildFlatList();

        return view('sisichakuna.categorias.create', compact('parents'));
    }

    public function store(Request $request)
    {
        // Detect when PHP silently dropped the request body (post_max_size exceeded)
        if (
            (int) $request->server('CONTENT_LENGTH') > 0 &&
            empty($request->all()) &&
            $request->files->count() === 0
        ) {
            $maxMb = (int) ini_get('upload_max_filesize');

            return back()->withErrors(['video' => "El archivo supera el límite permitido ({$maxMb}MB)."]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'pdfs.*' => 'nullable|file|mimes:pdf|max:20480',
            'docs.*' => ['nullable', File::types(['doc', 'docx'])->max(20480)],
            'audios.*' => 'nullable|file|mimes:mp3,wav,ogg,mpeg|max:51200',
            'videos.*' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v,video/x-mp4,video/mpeg|max:204800',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (empty($validated['order'])) {
            $validated['order'] = Category::where('parent_id', $validated['parent_id'] ?? null)->max('order') + 1;
        }

        $slug = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'), $slug);
        }

        $category = Category::create($validated);

        // Subir archivos múltiples a Google Drive
        $this->uploadFiles($request, $category, $slug);

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    public function publicIndex()
    {
        $tree = Category::with([
            'children.children.children.children',
            'children.files',
            'children.children.files',
            'children.children.children.files',
            'children.children.children.children.files',
        ])
            ->withCount('aportaciones')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('sisichakuna.show', compact('tree'));
    }

    public function show(Category $category)
    {
        if (! $category->is_active) {
            if (auth()->check()) {
                return redirect()->route('sisipedia.categories.admin-show', $category);
            }
            abort(404);
        }

        $category->load([
            'parent.parent.parent',
            'children.children',
            'children.files',
            'aportaciones' => fn ($q) => $q->where('is_approved', true)->latest(),
            'files',
        ]);

        $breadcrumbs = collect();
        $current = $category;

        while ($current) {
            $breadcrumbs->prepend($current);
            $current = $current->parent;
        }

        return view('sisichakuna.detalle', compact('category', 'breadcrumbs'));
    }

    public function adminShow(Category $category)
    {
        $category->load([
            'parent.parent.parent',
            'children.children',
            'aportaciones',
            'files',
        ]);

        $breadcrumbs = collect();
        $current = $category;

        while ($current) {
            $breadcrumbs->prepend($current);
            $current = $current->parent;
        }

        return view('sisichakuna.categorias.detalle', compact('category', 'breadcrumbs'));
    }

    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['categories' => [], 'aportaciones' => []]);
        }

        $categories = Category::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->with(['parent.parent.parent', 'files'])
            ->limit(20)
            ->get();

        $aportaciones = Aportacion::where(function ($query) use ($q) {
            $query->where('nombre_ol', 'like', "%{$q}%")
                ->orWhere('institucion', 'like', "%{$q}%")
                ->orWhere('ubicacion', 'like', "%{$q}%")
                ->orWhere('detalle', 'like', "%{$q}%");
        })
            ->with('category.parent.parent')
            ->limit(20)
            ->get();

        return response()->json([
            'categories' => $categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description ? Str::limit($c->description, 110) : null,
                'url' => route('sisipedia.categories.show', $c),
                'breadcrumb' => $c->path,
                'image' => $c->image ? asset($c->image) : null,
                'pdf' => $c->files->where('tipo', 'pdf')->isNotEmpty(),
                'doc' => $c->files->where('tipo', 'doc')->isNotEmpty(),
                'audio' => $c->files->where('tipo', 'audio')->isNotEmpty(),
                'video' => $c->files->where('tipo', 'video')->isNotEmpty(),
            ]),
            'aportaciones' => $aportaciones->map(fn ($a) => [
                'id' => $a->id,
                'nombre_ol' => $a->nombre_ol,
                'rol_nombre' => $a->rol_nombre,
                'institucion' => $a->institucion,
                'ubicacion' => $a->ubicacion,
                'detalle' => $a->detalle ? Str::limit($a->detalle, 110) : null,
                'pdf' => (bool) $a->pdf,
                'doc' => (bool) $a->doc,
                'audio' => (bool) $a->audio,
                'video' => (bool) $a->video,
                'category_name' => $a->category?->name,
                'category_url' => $a->category ? route('sisipedia.categories.show', $a->category) : null,
                'category_breadcrumb' => $a->category?->path,
            ]),
        ]);
    }

    public function registros()
    {
        return $this->publicIndex();
    }

    public function edit(Category $category)
    {
        $parents = $this->buildFlatList(exclude: $category->id);

        return view('sisichakuna.categorias.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        // Detect when PHP silently dropped the request body (post_max_size exceeded)
        if (
            (int) $request->server('CONTENT_LENGTH') > 0 &&
            empty($request->all()) &&
            $request->files->count() === 0
        ) {
            $maxMb = (int) ini_get('upload_max_filesize');

            return back()->withErrors(['video' => "El archivo supera el límite permitido ({$maxMb}MB)."]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'pdfs.*' => 'nullable|file|mimes:pdf|max:20480',
            'docs.*' => ['nullable', File::types(['doc', 'docx'])->max(20480)],
            'audios.*' => 'nullable|file|mimes:mp3,wav,ogg,mpeg|max:51200',
            'videos.*' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v,video/x-mp4,video/mpeg|max:204800',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // No se puede asignar a sí mismo como padre
        if (($validated['parent_id'] ?? null) == $category->id) {
            return back()->with('error', 'No se puede asignar una categoría como padre de sí misma.');
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (empty($validated['order'])) {
            $validated['order'] = Category::where('parent_id', $validated['parent_id'] ?? null)->max('order') + 1;
        }

        $slug = Str::slug($validated['name']);

        // --- Imagen ---
        if ($request->has('remove_image') && $request->remove_image == 1) {
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }
            $validated['image'] = null;
        }
        if ($request->hasFile('image')) {
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }
            $validated['image'] = $this->storeImage($request->file('image'), $slug);
        }

        $category->update($validated);

        // Subir archivos nuevos
        $this->uploadFiles($request, $category, $slug);

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una categoría que tiene subcategorías.');
        }

        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        foreach ($category->files as $file) {
            $this->drive->delete($file->drive_id);
        }

        $category->delete();

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }

    public function destroyFile(Category $category, CategoryFile $file)
    {
        $this->drive->delete($file->drive_id);
        $file->delete();

        return back()->with('success', 'Archivo eliminado correctamente.');
    }

    public function getChildren(Category $category)
    {
        $children = $category->children()->active()->get();

        return response()->json($children);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:categories,id',
            'order.*.order' => 'required|integer',
        ]);

        foreach ($request->order as $item) {
            Category::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Orden actualizado correctamente.']);
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        $status = $category->is_active ? 'activada' : 'desactivada';

        return back()->with('success', "Categoría {$status} exitosamente.");
    }

    // -------------------------------------------------------
    // Helper privado: sube múltiples archivos a Google Drive
    // -------------------------------------------------------
    private function uploadFiles(Request $request, Category $category, string $slug): void
    {
        $tipos = [
            'pdfs' => ['tipo' => 'pdf',   'folder' => 'pdf'],
            'docs' => ['tipo' => 'doc',   'folder' => 'doc'],
            'audios' => ['tipo' => 'audio', 'folder' => 'audio'],
            'videos' => ['tipo' => 'video', 'folder' => 'video'],
        ];

        foreach ($tipos as $inputName => $config) {
            if (! $request->hasFile($inputName)) {
                continue;
            }

            $orden = $category->files()->where('tipo', $config['tipo'])->max('orden') ?? -1;

            foreach ($request->file($inputName) as $file) {
                $ext = $file->getClientOriginalExtension();
                $original = $file->getClientOriginalName();
                $nombre = $slug.'-'.$config['tipo'].'-'.uniqid().'.'.$ext;

                $driveId = $this->drive->upload($file, $config['folder'], $nombre);

                CategoryFile::create([
                    'category_id' => $category->id,
                    'tipo' => $config['tipo'],
                    'drive_id' => $driveId,
                    'nombre_original' => $original,
                    'orden' => ++$orden,
                ]);
            }
        }
    }

    // -------------------------------------------------------
    // Helper privado: lista plana de categorías en orden árbol
    // -------------------------------------------------------
    private function buildFlatList(?int $exclude = null): \Illuminate\Support\Collection
    {
        $all = Category::orderBy('parent_id')->orderBy('order')->get()->keyBy('id');

        $flat = collect();

        $walk = function ($parentId, $depth) use (&$walk, &$flat, $all, $exclude) {
            foreach ($all->where('parent_id', $parentId)->sortBy('order') as $cat) {
                if ($cat->id === $exclude) {
                    continue;
                }
                $cat->depth = $depth;
                $flat->push($cat);
                $walk($cat->id, $depth + 1);
            }
        };

        $walk(null, 0);

        return $flat;
    }

    // -------------------------------------------------------
    // Helper privado: guarda imagen en public/img/sisipedia/
    // -------------------------------------------------------
    private function storeImage(\Illuminate\Http\UploadedFile $file, string $slug): string
    {
        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = "{$slug}-img.{$extension}";
        $directorio = public_path('img/sisipedia');
        $rutaCompleta = "{$directorio}/{$nombreArchivo}";

        $counter = 1;
        while (file_exists($rutaCompleta)) {
            $nombreArchivo = "{$slug}-img-{$counter}.{$extension}";
            $rutaCompleta = "{$directorio}/{$nombreArchivo}";
            $counter++;
        }

        if (! file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $file->move($directorio, $nombreArchivo);

        return "img/sisipedia/{$nombreArchivo}";
    }
}
