<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Importante para criptografar a senha

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 📦 Seed de Categorias
        \App\Models\Category::updateOrCreate(['id' => 1], ['name' => 'Eletrônicos']);
        \App\Models\Category::updateOrCreate(['id' => 2], ['name' => 'Mobiliário']);
        \App\Models\Category::updateOrCreate(['id' => 3], ['name' => 'Software/Licenças']);
        \App\Models\Category::updateOrCreate(['id' => 4], ['name' => 'Suprimentos']);

        // 👤 Seed do Usuário ADMINISTRADOR
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@innyx.com'], // Busca pelo e-mail
            [
                'name'     => 'Admin Innyx',
                'password' => Hash::make('password'), // Senha padrão: password
                'role'     => 'admin', // O campo que acabamos de criar na migration!
            ]
        );
        
        // 👥 Seed de um Usuário COMUM (Opcional, para você testar se o botão some)
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