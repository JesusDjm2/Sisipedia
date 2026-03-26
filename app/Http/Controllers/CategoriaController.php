<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Seccion;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
       /*  $categorias = Categoria::all(); */
       $categorias = Categoria::with('seccion')->withCount('libros')->get();
        return view('libros.categorias.index', compact('categorias'));
    }
    public function create()
    {
        $secciones = Seccion::all();
        return view('libros.categorias.create', compact('secciones'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'seccion_id' => 'required|exists:seccions,id',
        ]);

        Categoria::create($request->all());

        return redirect()->route('categorias.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function detallesCat($id)
    {
        /* $categoria = Categoria::with('libros')->findOrFail($id); */
        $categoria = Categoria::with([
            'libros' => function ($query) {
                $query->orderBy('nombre', 'asc');
            }
        ])->findOrFail($id); 
        return view('libros.categorias.detalles', compact('categoria'));
    }

    public function show($url)
    {
        $secciones = Seccion::orderBy('nombre', 'asc')->get();
        $categoria = Categoria::where('url', $url)->firstOrFail();
        $libros = $categoria->libros; 
        return view('libros.categorias.show', compact('categoria', 'secciones', 'libros'));
    }

    // Mostrar el formulario para editar una categoría existente
    public function edit($id)
    {
        $categoria = Categoria::findOrFail($id);
        $secciones = Seccion::all(); // Asumiendo que necesitas secciones para un dropdown
        return view('libros.categorias.edit', compact('categoria', 'secciones'));
    }

    // Actualizar una categoría existente en la base de datos
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'seccion_id' => 'required|exists:seccions,id',
        ]);

        $categoria = Categoria::findOrFail($id);
        $categoria->update($request->all());

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    // Eliminar una categoría existente
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}
