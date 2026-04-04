<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
{
    // Ajustado para manter o que você já tem (Eletrônicos) e adicionar as novas
    \App\Models\Category::updateOrCreate(['id' => 1], ['name' => 'Eletrônicos']);
    \App\Models\Category::updateOrCreate(['id' => 2], ['name' => 'Mobiliário']);
    \App\Models\Category::updateOrCreate(['id' => 3], ['name' => 'Software/Licenças']);
    \App\Models\Category::updateOrCreate(['id' => 4], ['name' => 'Suprimentos']);
}
}
