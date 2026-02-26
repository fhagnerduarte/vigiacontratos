<?php

namespace Tests\Feature\Api;

use App\Jobs\DispararWebhookJob;
use App\Models\User;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ApiWebhookTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->setUpTenant();

        $this->adminUser = $this->createAdminUser();
        Sanctum::actingAs($this->adminUser);
    }

    private function apiHeaders(): array
    {
        return ['X-Tenant-Slug' => 'testing'];
    }

    // --- CRUD Webhooks ---

    public function test_criar_webhook(): void
    {
        $response = $this->postJson('/api/v1/webhooks', [
            'url' => 'https://example.com/webhook',
            'eventos' => ['contrato.criado', 'alerta.gerado'],
            'descricao' => 'Webhook de teste',
        ], $this->apiHeaders());

        $response->assertStatus(201)
            ->assertJsonStructure(['data', 'secret', 'message']);

        $this->assertNotEmpty($response->json('secret'));
        $this->assertEquals(64, strlen($response->json('secret')));
    }

    public function test_criar_webhook_com_evento_invalido(): void
    {
        $response = $this->postJson('/api/v1/webhooks', [
            'url' => 'https://example.com/webhook',
            'eventos' => ['evento.invalido'],
        ], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['eventos.0']);
    }

    public function test_criar_webhook_sem_url_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/webhooks', [
            'eventos' => ['contrato.criado'],
        ], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_listar_webhooks(): void
    {
        Webhook::create([
            'url' => 'https://example.com/wh1',
            'eventos' => ['contrato.criado'],
            'secret' => WebhookService::gerarSecret(),
            'is_ativo' => true,
        ]);

        $response = $this->getJson('/api/v1/webhooks', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_show_webhook(): void
    {
        $webhook = Webhook::create([
            'url' => 'https://example.com/wh-show',
            'eventos' => ['contrato.criado'],
            'secret' => WebhookService::gerarSecret(),
            'is_ativo' => true,
        ]);

        $response = $this->getJson("/api/v1/webhooks/{$webhook->id}", $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.url', 'https://example.com/wh-show');
    }

    public function test_atualizar_webhook(): void
    {
        $webhook = Webhook::create([
            'url' => 'https://example.com/wh-update',
            'eventos' => ['contrato.criado'],
            'secret' => WebhookService::gerarSecret(),
            'is_ativo' => true,
        ]);

        $response = $this->putJson("/api/v1/webhooks/{$webhook->id}", [
            'url' => 'https://example.com/wh-updated',
            'is_ativo' => false,
        ], $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.url', 'https://example.com/wh-updated')
            ->assertJsonPath('data.is_ativo', false);
    }

    public function test_excluir_webhook(): void
    {
        $webhook = Webhook::create([
            'url' => 'https://example.com/wh-delete',
            'eventos' => ['contrato.criado'],
            'secret' => WebhookService::gerarSecret(),
            'is_ativo' => true,
        ]);

        $response = $this->deleteJson("/api/v1/webhooks/{$webhook->id}", [], $this->apiHeaders());

        $response->assertStatus(204);
        $this->assertNull(Webhook::find($webhook->id));
    }

    public function test_listar_eventos_disponiveis(): void
    {
        $response = $this->getJson('/api/v1/webhooks/eventos', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure(['eventos']);

        $this->assertContains('contrato.criado', $response->json('eventos'));
        $this->assertContains('alerta.gerado', $response->json('eventos'));
    }

    // --- WebhookService ---

    public function test_gerar_assinatura_hmac(): void
    {
        $payload = '{"event":"contrato.criado","data":{"id":1}}';
        $secret = 'test-secret-key';

        $assinatura = WebhookService::gerarAssinatura($payload, $secret);

        $this->assertNotEmpty($assinatura);
        $this->assertTrue(WebhookService::validarAssinatura($payload, $secret, $assinatura));
    }

    public function test_assinatura_invalida_nao_valida(): void
    {
        $payload = '{"event":"contrato.criado"}';
        $secret = 'test-secret-key';

        $this->assertFalse(WebhookService::validarAssinatura($payload, $secret, 'assinatura-errada'));
    }

    public function test_disparar_webhook_enfileira_job(): void
    {
        Queue::fake();

        Webhook::create([
            'url' => 'https://example.com/wh-dispatch',
            'eventos' => ['contrato.criado'],
            'secret' => WebhookService::gerarSecret(),
            'is_ativo' => true,
        ]);

        WebhookService::disparar('contrato.criado', ['id' => 1, 'numero' => '001/2026']);

        Queue::assertPushed(DispararWebhookJob::class, function ($job) {
            return $job->evento === 'contrato.criado';
        });
    }

    public function test_webhook_inativo_nao_dispara(): void
    {
        // Desativa webhooks existentes para isolar este teste
        Webhook::query()->update(['is_ativo' => false]);

        Queue::fake();

        Webhook::create([
            'url' => 'https://example.com/wh-inactive',
            'eventos' => ['contrato.criado'],
            'secret' => WebhookService::gerarSecret(),
            'is_ativo' => false,
        ]);

        WebhookService::disparar('contrato.criado', ['id' => 1]);

        Queue::assertNotPushed(DispararWebhookJob::class);
    }

    public function test_webhook_evento_diferente_nao_dispara(): void
    {
        // Desativa webhooks existentes para isolar este teste
        Webhook::query()->update(['is_ativo' => false]);

        Queue::fake();

        Webhook::create([
            'url' => 'https://example.com/wh-other',
            'eventos' => ['alerta.gerado'],
            'secret' => WebhookService::gerarSecret(),
            'is_ativo' => true,
        ]);

        WebhookService::disparar('contrato.criado', ['id' => 1]);

        Queue::assertNotPushed(DispararWebhookJob::class);
    }

    public function test_webhook_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/webhooks/99999', $this->apiHeaders());

        $response->assertStatus(404);
    }
}
