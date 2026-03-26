<?php

namespace App\Http\Controllers\sisipedia;

use App\Http\Controllers\Controller;
use App\Models\sisipedia\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;

class CategoryController extends Controller
{
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
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Manejar la imagen directamente en public/img/sisipedia/
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Generar nombre del archivo: nombre-categoria-img.extensión
            $nombreCategoria = Str::slug($validated['name']);
            $extension = $file->getClientOriginalExtension();
            $nombreArchivo = $nombreCategoria.'-img.'.$extension;

            // Verificar si ya existe y evitar duplicados
            $counter = 1;
            $rutaCompleta = public_path('img/sisipedia/'.$nombreArchivo);
            while (file_exists($rutaCompleta)) {
                $nombreArchivo = $nombreCategoria.'-img-'.$counter.'.'.$extension;
                $rutaCompleta = public_path('img/sisipedia/'.$nombreArchivo);
                $counter++;
            }

            // Crear directorio si no existe
            $directorio = public_path('img/sisipedia');
            if (! file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }

            // Mover la imagen al directorio public
            $file->move($directorio, $nombreArchivo);

            // Guardar la ruta relativa en la base de datos
            $validated['image'] = 'img/sisipedia/'.$nombreArchivo;
        }

        // Generar slug si no se proporcionó
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Asignar orden si no existe
        if (empty($validated['order'])) {
            $validated['order'] = Category::where('parent_id', $validated['parent_id'] ?? null)->max('order') + 1;
        }

        $category = Category::create($validated);

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $tree = Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('sisichakuna.show', compact('tree'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $parents = Category::parents()
            ->where('id', '!=', $category->id)
            ->orderBy('order')
            ->get();

        return view('sisichakuna.categorias.edit', compact('category', 'parents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Verificar que no se asigne a sí mismo como padre
        if ($validated['parent_id'] == $category->id) {
            return back()->with('error', 'No se puede asignar una categoría como padre de sí misma.');
        }

        // Manejar eliminación de imagen
        if ($request->has('remove_image') && $request->remove_image == 1) {
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }
            $validated['image'] = null;
        }

        // Manejar nueva imagen
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }

            $file = $request->file('image');

            // Generar nombre del archivo basado en el nombre de la categoría
            $nombreCategoria = Str::slug($validated['name']);
            $extension = $file->getClientOriginalExtension();
            $nombreArchivo = $nombreCategoria.'-img.'.$extension;

            // Verificar si ya existe y evitar duplicados
            $counter = 1;
            $rutaCompleta = public_path('img/sisipedia/'.$nombreArchivo);
            while (file_exists($rutaCompleta)) {
                $nombreArchivo = $nombreCategoria.'-img-'.$counter.'.'.$extension;
                $rutaCompleta = public_path('img/sisipedia/'.$nombreArchivo);
                $counter++;
            }

            // Crear directorio si no existe
            $directorio = public_path('img/sisipedia');
            if (! file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }

            // Mover la imagen al directorio public
            $file->move($directorio, $nombreArchivo);

            // Guardar la ruta relativa en la base de datos
            $validated['image'] = 'img/sisipedia/'.$nombreArchivo;
        }

        // Generar slug si no se proporcionó
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Si no se proporcionó orden, asignar automático
        if (empty($validated['order'])) {
            $validated['order'] = Category::where('parent_id', $validated['parent_id'] ?? null)->max('order') + 1;
        }

        $category->update($validated);

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Verificar si tiene hijos
        if ($category->children()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una categoría que tiene subcategorías.');
        }

        // Eliminar imagen si existe
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        $category->delete();

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }

    /**
     * Get children categories for AJAX.
     */
    public function getChildren(Category $category)
    {
        $children = $category->children()->active()->get();

        return response()->json($children);
    }

    /**
     * Reorder categories.
     */
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

    /**
     * Toggle category status.
     */
    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        $status = $category->is_active ? 'activada' : 'desactivada';

        return back()->with('success', "Categoría {$status} exitosamente.");
    }
}
