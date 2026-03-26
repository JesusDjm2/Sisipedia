<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Libro;
use App\Models\Seccion;
use Illuminate\Http\Request;

class LibroController extends Controller
{
    public function index()
    {
        $libros = Libro::with('categorias')->get();
        return view('libros.index', compact('libros'));
    }
    public function libros()
    {
        $secciones = Seccion::orderBy('nombre', 'asc')->get();
        $libros = Libro::orderBy('created_at', 'desc')->get();
        return view('libros.lista', compact('libros', 'secciones'));
    }

    public function create($categoriaId = null)
    {
        $secciones = Seccion::with('categorias')->get();
        $categorias = Categoria::all();
        $categoriaSeleccionada = $categoriaId ? [$categoriaId] : [];

        return view('libros.create', compact('secciones', 'categorias', 'categoriaSeleccionada'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'identificador' => 'required|string|max:255',
            'categorias' => 'required|array',
            'categorias.*' => 'exists:categorias,id'
        ]);

        $libro = Libro::create($request->only('nombre', 'autor', 'identificador'));
        $libro->categorias()->attach($request->categorias);

        return redirect()->route('libros.index')->with('success', 'Libro creado exitosamente.');
    }

    public function show($id)
    {
        $secciones = Seccion::all();
        $libros = Libro::all();
        $libro = Libro::findOrFail($id);
        return view('libros.show', compact('libro', 'secciones', 'libros'));
    }

    public function edit($id)
    {
        $libro = Libro::findOrFail($id);
        $categorias = Categoria::all();
        $libroCategorias = $libro->categorias->pluck('id')->toArray(); // Obteniendo los IDs de las categorías asociadas al libro
        return view('libros.edit', compact('libro', 'categorias', 'libroCategorias'));
    }

    public function update(Request $request, $id)
    {
        $libro = Libro::findOrFail($id);

        // Validación de los datos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'identificador' => 'required|string|max:255',
            'categorias' => 'array',
            'categorias.*' => 'exists:categorias,id',
        ]);

        // Actualizar los datos del libro
        $libro->update([
            'nombre' => $request->input('nombre'),
            'autor' => $request->input('autor'),
            'identificador' => $request->input('identificador'),
        ]);

        // Actualizar las categorías asociadas al libro
        if ($request->has('categorias')) {
            $libro->categorias()->sync($request->input('categorias'));
        } else {
            // Si no se seleccionó ninguna categoría, desasociar todas las categorías
            $libro->categorias()->detach();
        }

        return redirect()->route('libros.index')->with('success', 'Libro actualizado correctamente');
    }

    public function destroy($id)
    {
        $libro = Libro::findOrFail($id);
        $libro->delete();

        return redirect()->route('libros.index')->with('success', 'Libro eliminado correctamente');
    }

}
