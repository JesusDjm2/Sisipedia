<?php

namespace App\Http\Controllers\sisipedia;

use App\Http\Controllers\Controller;
use App\Models\sisipedia\Category;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(private GoogleDriveService $drive) {}

    public function index()
    {
        $categories = Category::with('parent')
            ->orderBy('parent_id')
            ->orderBy('order');
        $tree = Category::getTree();
        return view('sisichakuna.categorias.index', compact('categories', 'tree'));
    }

    public function create()
    {
        $parents = Category::parents()->orderBy('order')->get();

        return view('sisichakuna.categorias.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'pdf'         => 'nullable|file|mimes:pdf|max:20480',
            'audio'       => 'nullable|file|mimes:mp3,wav,ogg,mpeg|max:51200',
            'video'       => 'nullable|file|mimes:mp4,webm,mov|max:204800',
            'parent_id'   => 'nullable|exists:categories,id',
            'order'       => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Generar slug si no se proporcionó
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Asignar orden si no existe
        if (empty($validated['order'])) {
            $validated['order'] = Category::where('parent_id', $validated['parent_id'] ?? null)->max('order') + 1;
        }

        $slug = Str::slug($validated['name']);

        // Imagen → public/img/sisipedia/
        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'), $slug);
        }

        // PDF → Google Drive
        if ($request->hasFile('pdf')) {
            $ext = $request->file('pdf')->getClientOriginalExtension();
            $validated['pdf'] = $this->drive->upload(
                $request->file('pdf'),
                'pdf',
                "{$slug}-pdf.{$ext}"
            );
        }

        // Audio → Google Drive
        if ($request->hasFile('audio')) {
            $ext = $request->file('audio')->getClientOriginalExtension();
            $validated['audio'] = $this->drive->upload(
                $request->file('audio'),
                'audio',
                "{$slug}-audio.{$ext}"
            );
        }

        // Video → Google Drive
        if ($request->hasFile('video')) {
            $ext = $request->file('video')->getClientOriginalExtension();
            $validated['video'] = $this->drive->upload(
                $request->file('video'),
                'video',
                "{$slug}-video.{$ext}"
            );
        }

        Category::create($validated);

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    public function publicIndex()
    {
        $tree = Category::with('children')
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
        ]);

        $breadcrumbs = collect();
        $current = $category;

        while ($current) {
            $breadcrumbs->prepend($current);
            $current = $current->parent;
        }

        return view('sisichakuna.categorias.detalle', compact('category', 'breadcrumbs'));
    }

    public function registros()
    {
        return $this->publicIndex();
    }

    public function edit(Category $category)
    {
        $parents = Category::parents()
            ->where('id', '!=', $category->id)
            ->orderBy('order')
            ->get();

        return view('sisichakuna.categorias.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'pdf'         => 'nullable|file|mimes:pdf|max:20480',
            'audio'       => 'nullable|file|mimes:mp3,wav,ogg,mpeg|max:51200',
            'video'       => 'nullable|file|mimes:mp4,webm,mov|max:204800',
            'parent_id'   => 'nullable|exists:categories,id',
            'order'       => 'nullable|integer',
            'is_active'   => 'boolean',
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

        // --- PDF ---
        if ($request->has('remove_pdf') && $request->remove_pdf == 1) {
            if ($category->pdf) {
                $this->drive->delete($category->pdf);
            }
            $validated['pdf'] = null;
        }
        if ($request->hasFile('pdf')) {
            if ($category->pdf) {
                $this->drive->delete($category->pdf);
            }
            $ext = $request->file('pdf')->getClientOriginalExtension();
            $validated['pdf'] = $this->drive->upload(
                $request->file('pdf'),
                'pdf',
                "{$slug}-pdf.{$ext}"
            );
        }

        // --- Audio ---
        if ($request->has('remove_audio') && $request->remove_audio == 1) {
            if ($category->audio) {
                $this->drive->delete($category->audio);
            }
            $validated['audio'] = null;
        }
        if ($request->hasFile('audio')) {
            if ($category->audio) {
                $this->drive->delete($category->audio);
            }
            $ext = $request->file('audio')->getClientOriginalExtension();
            $validated['audio'] = $this->drive->upload(
                $request->file('audio'),
                'audio',
                "{$slug}-audio.{$ext}"
            );
        }

        // --- Video ---
        if ($request->has('remove_video') && $request->remove_video == 1) {
            if ($category->video) {
                $this->drive->delete($category->video);
            }
            $validated['video'] = null;
        }
        if ($request->hasFile('video')) {
            if ($category->video) {
                $this->drive->delete($category->video);
            }
            $ext = $request->file('video')->getClientOriginalExtension();
            $validated['video'] = $this->drive->upload(
                $request->file('video'),
                'video',
                "{$slug}-video.{$ext}"
            );
        }

        $category->update($validated);

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una categoría que tiene subcategorías.');
        }

        // Eliminar imagen local
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        // Eliminar archivos de Google Drive
        if ($category->pdf) {
            $this->drive->delete($category->pdf);
        }
        if ($category->audio) {
            $this->drive->delete($category->audio);
        }
        if ($category->video) {
            $this->drive->delete($category->video);
        }

        $category->delete();

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }

    public function getChildren(Category $category)
    {
        $children = $category->children()->active()->get();

        return response()->json($children);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order'          => 'required|array',
            'order.*.id'     => 'required|exists:categories,id',
            'order.*.order'  => 'required|integer',
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
    // Helper privado: guarda imagen en public/img/sisipedia/
    // -------------------------------------------------------
    private function storeImage(\Illuminate\Http\UploadedFile $file, string $slug): string
    {
        $extension    = $file->getClientOriginalExtension();
        $nombreArchivo = "{$slug}-img.{$extension}";
        $directorio   = public_path('img/sisipedia');
        $rutaCompleta = "{$directorio}/{$nombreArchivo}";

        $counter = 1;
        while (file_exists($rutaCompleta)) {
            $nombreArchivo = "{$slug}-img-{$counter}.{$extension}";
            $rutaCompleta  = "{$directorio}/{$nombreArchivo}";
            $counter++;
        }

        if (! file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $file->move($directorio, $nombreArchivo);

        return "img/sisipedia/{$nombreArchivo}";
    }
}
