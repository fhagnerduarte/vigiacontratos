<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\ForceHttps;
use Illuminate\Http\Request;
use Tests\TestCase;

class ForceHttpsTest extends TestCase
{
    protected ForceHttps $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new ForceHttps();
    }

    public function test_redireciona_http_para_https_em_producao(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $request = Request::create('http://example.com/dashboard', 'GET');

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertStringStartsWith('https://', $response->headers->get('Location'));
    }

    public function test_nao_redireciona_https_em_producao(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $request = Request::create('https://example.com/dashboard', 'GET');
        $request->server->set('HTTPS', 'on');

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_nao_redireciona_em_ambiente_local(): void
    {
        app()->detectEnvironment(fn () => 'local');

        $request = Request::create('http://example.com/dashboard', 'GET');

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_nao_redireciona_em_ambiente_testing(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        $request = Request::create('http://example.com/dashboard', 'GET');

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_preserva_uri_no_redirect(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $request = Request::create('http://example.com/admin-saas/tenants?page=2', 'GET');

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(301, $response->getStatusCode());
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/admin-saas/tenants', $location);
        $this->assertStringContainsString('page=2', $location);
    }

    public function test_redirect_permanente_301(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $request = Request::create('http://example.com/login', 'GET');

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(301, $response->getStatusCode());
    }
}
