<?php

namespace App\Models\sisipedia;

use Illuminate\Database\Eloquent\Model;

class Aportacion extends Model
{
    protected $table = 'aportaciones';

    /** Roles en formulario general (portada) y en vista pública de cada registro */
    public const ROLES = ['Equipo Puklla', 'Docente', 'Líder', 'Niño/Estudiante'];

    /** Roles permitidos cuando el aporte está ligado a una categoría concreta (misma lista que en portada) */
    public const ROLES_CON_REGISTRO = ['Equipo Puklla', 'Docente', 'Líder', 'Niño/Estudiante'];

    /** Etiqueta visible para el rol almacenado como «Líder» */
    public const ROL_LIDER_ETIQUETA = 'Lidereza/Líder';

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

    /** Texto para mostrar el rol (p. ej. Líder → Lidereza/Líder). */
    public static function etiquetaRol(?string $rolNombre): string
    {
        if ($rolNombre === 'Líder') {
            return self::ROL_LIDER_ETIQUETA;
        }

        return $rolNombre ?? '';
    }
}
