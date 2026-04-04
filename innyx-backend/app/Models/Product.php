<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Adicionados os campos obrigatórios para permitir a gravação no banco
    protected $fillable = [
        'name', 
        'price', 
        'description', 
        'image',
        'expiration_date', // 
        'category_id'      // 
    ];

    // Relacionamento: Um produto pertence a uma categoria 
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}