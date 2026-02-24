<?php

namespace Tests\Feature\Commands;

use App\Models\Tenant;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;

class TenantCreateCommandTest extends TestCase
{
    use RunsTenantMigrations;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_slug_duplicado_retorna_erro(): void
    {
        $slug = 'slug-existente-' . uniqid();
        Tenant::create([
            'nome' => 'Tenant Existente',
            'slug' => $slug,
            'database_name' => 'db_existente_' . uniqid(),
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('tenant:create', ['nome' => 'Duplicado', 'slug' => $slug])
            ->expectsOutputToContain('já existe')
            ->assertExitCode(1);
    }

    public function test_database_duplicada_retorna_erro(): void
    {
        $dbName = 'vigiacontratos_prefeitura_dup';
        Tenant::create([
            'nome' => 'Tenant DB Existente',
            'slug' => 'slug-db-existente-' . uniqid(),
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('tenant:create', ['nome' => 'Duplicado DB', 'slug' => 'prefeitura-dup'])
            ->expectsOutputToContain('já registrado')
            ->assertExitCode(1);
    }

    public function test_criacao_com_sucesso_exibe_mensagem(): void
    {
        $slug = 'prefeitura-nova-' . uniqid();

        // O command faz DB::purge e CREATE DATABASE que quebra transacao.
        // Testar apenas via output
        $this->artisan('tenant:create', ['nome' => 'Prefeitura Nova', 'slug' => $slug])
            ->expectsOutputToContain('criado com sucesso')
            ->assertExitCode(0);
    }
}
