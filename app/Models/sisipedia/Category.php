<?php

namespace App\Models\sisipedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

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
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
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

    public function getPathAttribute()
    {
        $path = collect();

        $category = $this;
        while ($category) {
            $path->prepend($category->name);
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
            // Obtener el orden de esta categoría entre sus hermanos
            $siblings = Category::where('parent_id', $current->parent_id)
                ->orderBy('order')
                ->get();
            // Encontrar la posición (índice comenzando en 1)
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
