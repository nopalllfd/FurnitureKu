<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categories;


class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'is_active',
        'category_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'stock' => 'integer',
    ];

    // Menambahkan image_url otomatis ke response JSON
    protected $appends = ['image_url'];

    /**
     * Relasi ke kategori
     */
    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    /**
     * Accessor untuk image_url
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/uploads/products/' . $this->image);
        }

        return null;
    }
}
