<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use Illuminate\Http\Request;

class SeccionController extends Controller
{
    public function index()
    {
        /* $secciones = Seccion::with('categorias')->get(); */
        $secciones = Seccion::with([
            'categorias' => function ($query) {
                $query->withCount('libros');
            }
        ])->get();
        return view('libros.secciones.index', compact('secciones'));
    }
    public function create()
    {
        return view('libros.secciones.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'url' => 'required|string|max:255',
        ]);

        Seccion::create($request->all());

        return redirect()->route('secciones.index')->with('success', 'Sección creada exitosamente.');
    }

    // Mostrar una sección específica
    public function show($id)
    {
        $seccion = Seccion::findOrFail($id);
        /* $categorias = $seccion->categorias; */
        $categorias = $seccion->categorias()->withCount('libros')->get();

        return view('libros.secciones.show', compact('seccion', 'categorias'));
    }

    // Mostrar el formulario para editar una sección específica
    public function edit($id)
    {
        $seccion = Seccion::findOrFail($id);
        return view('libros.secciones.edit', compact('seccion'));
    }


    // Actualizar una sección específica en la base de datos
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'url' => 'required|string|max:255',
        ]);

        $seccion = Seccion::findOrFail($id);
        $seccion->update($request->all());

        return redirect()->route('secciones.index')->with('success', 'Sección actualizada con éxito');
    }

    // Eliminar una sección específica de la base de datos
    public function destroy($id)
    {
        $seccion = Seccion::findOrFail($id);
        $seccion->delete();

        return redirect()->route('secciones.index')->with('success', 'Sección eliminada exitosamente.');
    }
}
