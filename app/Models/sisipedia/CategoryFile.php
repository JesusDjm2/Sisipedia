<?php

namespace App\Models\sisipedia;

use Illuminate\Database\Eloquent\Model;

class CategoryFile extends Model
{
    protected $fillable = ['category_id', 'tipo', 'drive_id', 'nombre_original', 'orden'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Etiqueta legible para mostrar en vista */
    public function getNombreDisplayAttribute(): string
    {
        return $this->nombre_original
            ?? ucfirst($this->tipo) . ' ' . $this->id;
    }
}
