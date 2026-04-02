<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Adicione esta linha para permitir o cadastro do nome
    protected $fillable = ['name'];
}



