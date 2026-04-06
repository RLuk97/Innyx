<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Importante para criptografar a senha

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        \App\Models\Category::updateOrCreate(['id' => 1], ['name' => 'Eletrônicos']);
        \App\Models\Category::updateOrCreate(['id' => 2], ['name' => 'Mobiliário']);
        \App\Models\Category::updateOrCreate(['id' => 3], ['name' => 'Software/Licenças']);
        \App\Models\Category::updateOrCreate(['id' => 4], ['name' => 'Suprimentos']);
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@innyx.com'], // Busca pelo e-mail
            [
                'name'     => 'Admin Innyx',
                'password' => Hash::make('password'), // Senha padrão: password
                'role'     => 'admin', // O campo que acabamos de criar na migration!
            ]
        );
        
        \App\Models\User::updateOrCreate(
            ['email' => 'user@innyx.com'],
            [
                'name'     => 'User Teste',
                'password' => Hash::make('password'),
                'role'     => 'user', 
            ]
        );
    }
}
