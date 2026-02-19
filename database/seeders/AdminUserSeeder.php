<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@vigiacontratos.com.br');

        if (AdminUser::where('email', $email)->exists()) {
            return;
        }

        AdminUser::create([
            'nome' => env('ADMIN_NAME', 'Admin SaaS'),
            'email' => $email,
            'password' => env('ADMIN_PASSWORD', 'Mudar@2026!'),
            'is_ativo' => true,
        ]);
    }
}
