<?php

namespace Tests\Feature\UX;

use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ToastNotificationTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    // ==========================================
    // Toast Component
    // ==========================================

    public function test_toast_component_renderiza_com_session_success(): void
    {
        $view = $this->blade('<x-toast />', []);
        $view->assertDontSee('toast-container');

        // Com session flash
        session()->flash('success', 'Operacao realizada com sucesso');
        $view = $this->blade('<x-toast />');
        $view->assertSee('toast-container');
        $view->assertSee('Operacao realizada com sucesso');
        $view->assertSee('text-success');
        $view->assertSee('border-success');
    }

    public function test_toast_component_renderiza_com_session_error(): void
    {
        session()->flash('error', 'Falha na operacao');
        $view = $this->blade('<x-toast />');
        $view->assertSee('toast-container');
        $view->assertSee('Falha na operacao');
        $view->assertSee('text-danger');
        $view->assertSee('border-danger');
    }

    public function test_toast_component_renderiza_com_session_warning(): void
    {
        session()->flash('warning', 'Atencao necessaria');
        $view = $this->blade('<x-toast />');
        $view->assertSee('toast-container');
        $view->assertSee('Atencao necessaria');
        $view->assertSee('text-warning');
        $view->assertSee('border-warning');
    }

    public function test_toast_component_renderiza_com_session_info(): void
    {
        session()->flash('info', 'Informacao importante');
        $view = $this->blade('<x-toast />');
        $view->assertSee('toast-container');
        $view->assertSee('Informacao importante');
        $view->assertSee('text-info');
        $view->assertSee('border-info');
    }

    public function test_toast_component_suporta_session_status(): void
    {
        session()->flash('status', 'Email enviado com sucesso');
        $view = $this->blade('<x-toast />');
        $view->assertSee('toast-container');
        $view->assertSee('Email enviado com sucesso');
    }

    public function test_toast_component_empilha_multiplas_mensagens(): void
    {
        session()->flash('success', 'Sucesso');
        session()->flash('warning', 'Aviso');
        $view = $this->blade('<x-toast />');
        $view->assertSee('Sucesso');
        $view->assertSee('Aviso');
    }

    public function test_toast_component_nao_renderiza_sem_flash(): void
    {
        $view = $this->blade('<x-toast />');
        $view->assertDontSee('toast-container');
    }

    public function test_toast_tem_auto_dismiss_5s(): void
    {
        session()->flash('success', 'Teste');
        $view = $this->blade('<x-toast />');
        $view->assertSee('data-bs-delay', false);
        $view->assertSee('data-bs-autohide', false);
    }

    public function test_toast_tem_botao_fechar(): void
    {
        session()->flash('success', 'Teste');
        $view = $this->blade('<x-toast />');
        $view->assertSee('btn-close', false);
        $view->assertSee('data-bs-dismiss', false);
    }

    // ==========================================
    // Layout Integration
    // ==========================================

    public function test_layout_principal_inclui_toast_component(): void
    {
        $content = file_get_contents(resource_path('views/layout/layout.blade.php'));
        $this->assertStringContainsString('<x-toast />', $content);
    }

    public function test_layout_admin_inclui_toast_component(): void
    {
        $content = file_get_contents(resource_path('views/layout/admin-saas.blade.php'));
        $this->assertStringContainsString('<x-toast />', $content);
    }

    public function test_layout_auth_inclui_toast_component(): void
    {
        $content = file_get_contents(resource_path('views/layout/auth.blade.php'));
        $this->assertStringContainsString('<x-toast />', $content);
    }

    // ==========================================
    // Scripts Registration
    // ==========================================

    public function test_script_component_carrega_sweetalert2(): void
    {
        $content = file_get_contents(resource_path('views/components/script.blade.php'));
        $this->assertStringContainsString('sweetalert2', $content);
    }

    public function test_script_component_carrega_toast_init(): void
    {
        $content = file_get_contents(resource_path('views/components/script.blade.php'));
        $this->assertStringContainsString('toast-init.js', $content);
    }

    public function test_script_component_carrega_confirm_dialog(): void
    {
        $content = file_get_contents(resource_path('views/components/script.blade.php'));
        $this->assertStringContainsString('confirm-dialog.js', $content);
    }

    // ==========================================
    // Inline Alerts Removed
    // ==========================================

    public function test_views_tenant_nao_tem_alerts_inline(): void
    {
        $viewsDir = resource_path('views/tenant');
        $pattern = '/session\([\'"](?:success|error|warning)[\'"]\)/';
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewsDir)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                if (preg_match($pattern, $content)) {
                    $violations[] = str_replace(resource_path('views/'), '', $file->getPathname());
                }
            }
        }

        $this->assertEmpty(
            $violations,
            'As seguintes views ainda contem alerts inline: ' . implode(', ', $violations)
        );
    }

    // ==========================================
    // Confirm Dialogs Migrated
    // ==========================================

    public function test_views_nao_usam_confirm_nativo(): void
    {
        $viewsDir = resource_path('views');
        $pattern = '/return\s+confirm\s*\(/';
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewsDir)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                if (preg_match($pattern, $content)) {
                    $violations[] = str_replace(resource_path('views/'), '', $file->getPathname());
                }
            }
        }

        $this->assertEmpty(
            $violations,
            'As seguintes views ainda usam confirm() nativo: ' . implode(', ', $violations)
        );
    }

    public function test_forms_destrutivos_usam_data_confirm(): void
    {
        // Verifica que forms de delete usam data-confirm
        $viewsToCheck = [
            'tenant/contratos/index.blade.php',
            'tenant/fornecedores/index.blade.php',
            'tenant/servidores/index.blade.php',
            'tenant/secretarias/index.blade.php',
            'tenant/users/index.blade.php',
            'tenant/roles/index.blade.php',
        ];

        foreach ($viewsToCheck as $viewPath) {
            $content = file_get_contents(resource_path("views/{$viewPath}"));
            if (str_contains($content, '@method(\'DELETE\')') || str_contains($content, "@method('DELETE')")) {
                $this->assertStringContainsString(
                    'data-confirm',
                    $content,
                    "View {$viewPath} tem form DELETE sem data-confirm"
                );
            }
        }
    }

    // ==========================================
    // Assets existem
    // ==========================================

    public function test_sweetalert2_js_existe(): void
    {
        $this->assertFileExists(public_path('assets/js/lib/sweetalert2.all.min.js'));
    }

    public function test_toast_init_js_existe(): void
    {
        $this->assertFileExists(public_path('assets/js/toast-init.js'));
    }

    public function test_confirm_dialog_js_existe(): void
    {
        $this->assertFileExists(public_path('assets/js/confirm-dialog.js'));
    }
}
