<?php

namespace App\Models\sisipedia;

use Illuminate\Database\Eloquent\Model;

class Aportacion extends Model
{
    protected $table = 'aportaciones';

    /** Roles en formulario general (portada); en registro de categoría solo Docente, Líder, Niño/Estudiante */
    public const ROLES = ['Equipo Puklla', 'Docente', 'Líder', 'Niño/Estudiante'];

    /** Roles permitidos cuando el aporte está ligado a una categoría concreta */
    public const ROLES_CON_REGISTRO = ['Docente', 'Líder', 'Niño/Estudiante'];

    protected $fillable = [
        'category_id',
        'rol_nombre',
        'nombre_ol',
        'institucion',
        'ubicacion',
        'detalle',
        'pdf',
        'doc',
        'audio',
        'video',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
