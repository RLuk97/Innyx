<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
 
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // Máximo de 50 caracteres
            $table->string('description', 200); // Máximo de 200 caracteres
            $table->double('price'); // Valor positivo, double
            $table->date('expiration_date'); // Data de validade
            $table->string('image'); // Upload de imagem, nome único
            $table->foreignId('category_id')->constrained('categories'); // Categoria relacionada
            $table->timestamps();
    });
    }

    public function down()
    {
        Schema::dropIfExists('categories_and_products_tables');
    }
};
