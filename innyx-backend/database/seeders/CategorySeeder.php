<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Criando as categorias base para o sistema (ID fixo para sincronizar com o Vue)
        Category::create(['id' => 1, 'name' => 'Hardware']);
        Category::create(['id' => 2, 'name' => 'Software']);
        Category::create(['id' => 3, 'name' => 'Serviços']);
    }
}