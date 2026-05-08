<?php

namespace App\Models\sisipedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'parent_id',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug(self::stripLegacyOntologyPrefix($category->name));
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(CategoryFile::class)->orderBy('orden');
    }

    public function filesByType(string $tipo)
    {
        return $this->files()->where('tipo', $tipo)->get();
    }

    public function aportaciones()
    {
        return $this->hasMany(Aportacion::class)->latest();
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public static function getTree()
    {
        return self::with('children')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
    }

    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    /** Cantidad de nodos en este subárbol (esta categoría + todas sus descendencias). */
    public function subtreeSize(): int
    {
        $count = 0;
        $frontier = [$this->id];
        while (! empty($frontier)) {
            $count += count($frontier);
            $frontier = self::whereIn('parent_id', $frontier)->pluck('id')->all();
        }

        return $count;
    }

    /**
     * IDs de esta categoría y descendientes, orden hoja → raíz (sirve para borrar sin violar FKs).
     *
     * @return array<int>
     */
    public function subtreeIdsPostOrder(): array
    {
        $out = [];
        $childIds = self::where('parent_id', $this->id)->orderBy('order')->pluck('id');
        foreach ($childIds as $cid) {
            $child = self::find($cid);
            if ($child) {
                $out = array_merge($out, $child->subtreeIdsPostOrder());
            }
        }
        $out[] = $this->id;

        return $out;
    }

    /**
     * Quita prefijos ontológicos heredados (p. ej. "1 ", "2.3.1 - ", "10.") del nombre guardado en BD.
     */
    public static function stripLegacyOntologyPrefix(string $name): string
    {
        $trimmed = trim($name);
        $cleaned = preg_replace('/^\s*\d+(?:\.\d+)*\s*[-–.:\)]?\s*/u', '', $trimmed);

        return $cleaned !== '' ? $cleaned : $trimmed;
    }

    /** Nombre sin prefijo numérico ontológico (solo para pantallas; el campo `name` en BD puede seguir igual). */
    public function getDisplayNameAttribute(): string
    {
        return self::stripLegacyOntologyPrefix($this->attributes['name'] ?? '');
    }

    public function getPathAttribute()
    {
        $path = collect();

        $category = $this;
        while ($category) {
            $path->prepend($category->display_name);
            $category = $category->parent;
        }

        return $path->join(' > ');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getNumberingAttribute()
    {
        $numbers = [];
        $current = $this;
        while ($current) {
            $siblings = Category::where('parent_id', $current->parent_id)
                ->orderBy('order')
                ->get();
            $position = 1;
            foreach ($siblings as $index => $sibling) {
                if ($sibling->id === $current->id) {
                    $position = $index + 1;
                    break;
                }
            }

            array_unshift($numbers, $position);
            $current = $current->parent;
        }

        return implode('.', $numbers);
    }
}
