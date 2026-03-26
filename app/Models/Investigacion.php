<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investigacion extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'parent_id',
    ];

    // Padre
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // Hijos directos
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // 🔥 Hijos recursivos infinitos
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    // 🔥 Archivos PDF del nodo
    public function archivos()
    {
        return $this->hasMany(InvestigacionArchivo::class);
    }
}
