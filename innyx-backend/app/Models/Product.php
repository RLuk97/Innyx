<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    // Define quais campos podem ser preenchidos em massa
    protected $fillable = ['name', 'description', 'price', 'expiration_date', 'image', 'category_id'];

// Relacionamento: Um produto pertence a uma categoria
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
