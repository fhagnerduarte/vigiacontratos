<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('tenant')->table('users')->updateOrInsert(
            ['email' => 'admin@vigiacontratos.com.br'],
            [
                'nome'       => 'Administrador',
                'password'   => Hash::make('password'),
                'role_id'    => DB::connection('tenant')->table('roles')->where('nome', 'administrador_geral')->value('id'),
                'is_ativo'   => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
