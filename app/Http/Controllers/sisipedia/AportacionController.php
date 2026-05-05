<?php

namespace App\Http\Controllers\sisipedia;

use App\Http\Controllers\Controller;
use App\Models\sisipedia\Aportacion;
use App\Models\sisipedia\Category;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class AportacionController extends Controller
{
    public function __construct(private GoogleDriveService $drive)
    {
    }

    public function adminIndex(Request $request)
    {
        $roles = Aportacion::ROLES;
        $categories = Category::orderBy('name')->get(['id', 'name', 'parent_id']);

        $query = Aportacion::with('category.parent.parent')
            ->latest();

        if ($request->filled('rol')) {
            $query->where('rol_nombre', $request->rol);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'pendiente') {
                $query->where('is_approved', false);
            } elseif ($request->estado === 'aprobada') {
                $query->where('is_approved', true);
            }
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre_ol', 'like', "%{$q}%")
                    ->orWhere('institucion', 'like', "%{$q}%")
                    ->orWhere('ubicacion', 'like', "%{$q}%");
            });
        }

        /** @var LengthAwarePaginator $aportaciones */
        $aportaciones = $query->paginate(20);
        $aportaciones->withQueryString();

        $totals = Aportacion::selectRaw('rol_nombre, count(*) as total')
            ->groupBy('rol_nombre')
            ->pluck('total', 'rol_nombre');

        $pendientesGeneral = Aportacion::whereNull('category_id')
            ->where('is_approved', false)
            ->count();

        $categoryPickerRows = $this->categoriesForPicker();

        return view('sisichakuna.aportaciones.index', compact(
            'aportaciones', 'roles', 'categories', 'totals', 'pendientesGeneral', 'categoryPickerRows'
        ));
    }

    /**
     * Lista plana ordenada en profundidad primero, con ruta completa para el selector del modal.
     *
     * @return array<int, array{id:int, name:string, depth:int, path_label:string, search_blob:string, is_root:bool}>
     */
    private function categoriesForPicker(): array
    {
        $all = Category::query()
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $byId = $all->keyBy('id');

        $pathFor = function ($cat) use ($byId): string {
            $parts = [];
            $current = $cat;
            $guard = 0;
            while ($current && $guard++ < 64) {
                array_unshift($parts, $current->name);
                $pid = $current->parent_id;
                $current = $pid ? $byId->get($pid) : null;
            }

            return implode(' › ', $parts);
        };

        $byParent = $all->groupBy(fn ($c) => $c->parent_id ?? 0);

        $rows = [];
        $walk = function (int $parentKey, int $depth) use (&$walk, &$rows, $byParent, $pathFor): void {
            foreach ($byParent->get($parentKey, collect()) as $cat) {
                $pathLabel = $pathFor($cat);
                $rows[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'depth' => $depth,
                    'path_label' => $pathLabel,
                    'search_blob' => mb_strtolower($pathLabel.' '.$cat->name),
                    'is_root' => $depth === 0,
                ];
                $walk((int) $cat->id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $rows;
    }

    /** Aporte ligado a un registro (categoría); mismos roles que en la portada. */
    public function store(Request $request, Category $category)
    {
        if (
            (int) $request->server('CONTENT_LENGTH') > 0 &&
            empty($request->all()) &&
            $request->files->count() === 0
        ) {
            $maxMb = (int) ini_get('upload_max_filesize');

            return back()->withErrors(['video' => "El archivo supera el límite permitido ({$maxMb}MB). Usa un video más pequeño o comprime el archivo."])
                ->withInput();
        }

        $rolesCat = implode(',', Aportacion::ROLES_CON_REGISTRO);

        $request->validate([
            'rol_nombre' => "required|in:{$rolesCat}",
            'nombre_ol' => 'required|string|max:255',
            'institucion' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'detalle' => 'nullable|string',
            'pdf' => 'nullable|file|mimes:pdf|max:20480',
            'doc' => ['nullable', File::types(['doc', 'docx'])->max(20480)],
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,mpeg|max:51200',
            'video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v,video/x-mp4,video/mpeg|max:204800',
        ]);

        $data = $request->only(['rol_nombre', 'nombre_ol', 'institucion', 'ubicacion', 'detalle']);
        $data['category_id'] = $category->id;
        $data['is_approved'] = true;

        $prefix = $category->slug.'-'.Str::slug($request->nombre_ol);

        $data = array_merge($data, $this->uploadArchivos($request, $prefix));

        Aportacion::create($data);

        return back()->with('success', 'Aportación creada correctamente.');
    }

    /** Aporte desde la portada: sin categoría; queda pendiente hasta que un administrador apruebe. */
    public function storeStandalone(Request $request)
    {
        if (
            (int) $request->server('CONTENT_LENGTH') > 0 &&
            empty($request->all()) &&
            $request->files->count() === 0
        ) {
            $maxMb = (int) ini_get('upload_max_filesize');

            return redirect()->route('index')
                ->withFragment('home-aporte-standalone')
                ->withErrors(['video' => "El archivo supera el límite permitido ({$maxMb}MB)."])
                ->withInput();
        }

        $roles = implode(',', Aportacion::ROLES);

        $validator = Validator::make($request->all(), [
            'rol_nombre' => "required|in:{$roles}",
            'nombre_ol' => 'required|string|max:255',
            'institucion' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'detalle' => 'nullable|string',
            'pdf' => 'nullable|file|mimes:pdf|max:20480',
            'doc' => ['nullable', File::types(['doc', 'docx'])->max(20480)],
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,mpeg|max:51200',
            'video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v,video/x-mp4,video/mpeg|max:204800',
        ]);

        if ($validator->fails()) {
            return redirect()->route('index')
                ->withFragment('home-aporte-standalone')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only(['rol_nombre', 'nombre_ol', 'institucion', 'ubicacion', 'detalle']);
        $data['category_id'] = null;
        $data['is_approved'] = false;

        $prefix = 'general-'.Str::slug($request->nombre_ol).'-'.Str::random(4);

        $data = array_merge($data, $this->uploadArchivos($request, $prefix));

        Aportacion::create($data);

        return redirect()->route('index')
            ->withFragment('home-aporte-standalone')
            ->with(
                'aporte_success',
                'Tu aporte se ha enviado. El equipo lo revisará y, si corresponde, lo aprobará desde el panel de administración.'
            );
    }

    public function update(Request $request, Aportacion $aportacion)
    {
        $roles = implode(',', Aportacion::ROLES);

        $validated = $request->validate([
            'nombre_ol'   => 'required|string|max:255',
            'rol_nombre'  => "required|in:{$roles}",
            'institucion' => 'nullable|string|max:255',
            'ubicacion'   => 'nullable|string|max:255',
            'detalle'     => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $aportacion->update($validated);

        return back()->with('success', 'Aportación actualizada correctamente.');
    }

    public function approve(Request $request, Aportacion $aportacion)
    {
        $categoryId = $aportacion->category_id;

        if ($aportacion->category_id === null) {
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
            ]);
            $categoryId = (int) $validated['category_id'];
        }

        $aportacion->update([
            'is_approved' => true,
            'category_id' => $categoryId,
        ]);

        return back()->with('success', 'Aportación aprobada y vinculada al registro elegido.');
    }

    private function uploadArchivos(Request $request, string $prefix): array
    {
        $data = [];

        if ($request->hasFile('pdf')) {
            $ext = $request->file('pdf')->getClientOriginalExtension();
            $data['pdf'] = $this->drive->upload(
                $request->file('pdf'), 'pdf', "{$prefix}-pdf.{$ext}"
            );
        }

        if ($request->hasFile('doc')) {
            $ext = $request->file('doc')->getClientOriginalExtension();
            $data['doc'] = $this->drive->upload(
                $request->file('doc'), 'doc', "{$prefix}-doc.{$ext}"
            );
        }

        if ($request->hasFile('audio')) {
            $ext = $request->file('audio')->getClientOriginalExtension();
            $data['audio'] = $this->drive->upload(
                $request->file('audio'), 'audio', "{$prefix}-audio.{$ext}"
            );
        }

        if ($request->hasFile('video')) {
            $ext = $request->file('video')->getClientOriginalExtension();
            $data['video'] = $this->drive->upload(
                $request->file('video'), 'video', "{$prefix}-video.{$ext}"
            );
        }

        return $data;
    }

    public function destroy(Category $category, Aportacion $aportacion)
    {
        if ((int) $aportacion->category_id !== (int) $category->id) {
            abort(404);
        }

        return $this->eliminarAportacion($aportacion);
    }

    /** Eliminar aportación sin categoría o desde panel general */
    public function destroyStandalone(Aportacion $aportacion)
    {
        return $this->eliminarAportacion($aportacion);
    }

    private function eliminarAportacion(Aportacion $aportacion)
    {
        foreach (['pdf', 'doc', 'audio', 'video'] as $field) {
            if ($aportacion->$field) {
                $this->drive->delete($aportacion->$field);
            }
        }

        $aportacion->delete();

        return back()->with('success', 'Aportación eliminada correctamente.');
    }
}
