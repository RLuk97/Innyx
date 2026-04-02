<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tabela de Categorias - Requisito 2.19 & 2.20
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Nome: máximo de 100 caracteres
            $table->timestamps();
        });

        // Tabela de Produtos - Requisito 2.6 & 2.12 a 2.18
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories_and_products_tables');
    }
};
