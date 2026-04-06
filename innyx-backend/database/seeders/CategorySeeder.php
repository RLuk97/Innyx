<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        Category::create(['id' => 1, 'name' => 'Hardware']);
        Category::create(['id' => 2, 'name' => 'Software']);
        Category::create(['id' => 3, 'name' => 'Serviços']);
    }
}
