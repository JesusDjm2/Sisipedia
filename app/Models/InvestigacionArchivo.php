<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestigacionArchivo extends Model
{
    protected $fillable = [
        'investigacion_id',
        'nombre',
        'ruta',
        'mime',
        'peso',
    ];

    public function investigacion()
    {
        return $this->belongsTo(Investigacion::class);
    }
}
