<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Presentation\Http\Routes\Router;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private Router $router;
    private array $capturedParams;

    protected function setUp(): void
    {
        $this->router         = new Router();
        $this->capturedParams = [];
    }

    #[Test]
    public function dispatchCallsMatchingGetHandler(): void
    {
        $this->router->get('/contacts', function () {
            $this->capturedParams = ['called' => true];
        });

        ob_start();
        $this->router->dispatch('GET', '/contacts');
        ob_end_clean();

        $this->assertTrue($this->capturedParams['called']);
    }

    #[Test]
    public function dispatchExtractsNamedParameters(): void
    {
        $this->router->get('/contacts/{id}', function (string $id) {
            $this->capturedParams = ['id' => $id];
        });

        ob_start();
        $this->router->dispatch('GET', '/contacts/42');
        ob_end_clean();

        $this->assertSame('42', $this->capturedParams['id']);
    }

    #[Test]
    public function dispatchMatchesCorrectHttpMethod(): void
    {
        $this->router->get('/contacts', function () {
            $this->capturedParams = ['method' => 'GET'];
        });
        $this->router->post('/contacts', function () {
            $this->capturedParams = ['method' => 'POST'];
        });

        ob_start();
        $this->router->dispatch('POST', '/contacts');
        ob_end_clean();

        $this->assertSame('POST', $this->capturedParams['method']);
    }

    #[Test]
    public function dispatchSupportsPutMethod(): void
    {
        $this->router->put('/contacts/{id}', function (string $id) {
            $this->capturedParams = ['method' => 'PUT', 'id' => $id];
        });

        ob_start();
        $this->router->dispatch('PUT', '/contacts/5');
        ob_end_clean();

        $this->assertSame('PUT', $this->capturedParams['method']);
        $this->assertSame('5', $this->capturedParams['id']);
    }

    #[Test]
    public function dispatchSupportsDeleteMethod(): void
    {
        $this->router->delete('/contacts/{id}', function (string $id) {
            $this->capturedParams = ['id' => $id];
        });

        ob_start();
        $this->router->dispatch('DELETE', '/contacts/3');
        ob_end_clean();

        $this->assertSame('3', $this->capturedParams['id']);
    }

    #[Test]
    public function dispatchSupportsFluentInterface(): void
    {
        $result = $this->router
            ->get('/a', function () {})
            ->post('/b', function () {})
            ->put('/c', function () {})
            ->delete('/d', function () {});

        $this->assertInstanceOf(Router::class, $result);
    }

    #[Test]
    public function dispatchNormalizesTrailingSlash(): void
    {
        $this->router->get('/contacts', function () {
            $this->capturedParams = ['called' => true];
        });

        ob_start();
        $this->router->dispatch('GET', '/contacts/');
        ob_end_clean();

        $this->assertTrue($this->capturedParams['called']);
    }

    #[Test]
    public function dispatchStripsQueryString(): void
    {
        $this->router->get('/contacts', function () {
            $this->capturedParams = ['called' => true];
        });

        ob_start();
        $this->router->dispatch('GET', '/contacts?page=1&limit=10');
        ob_end_clean();

        $this->assertTrue($this->capturedParams['called']);
    }

    #[Test]
    public function dispatchWithBasePathStripsPrefix(): void
    {
        $router = new Router('/api/v1');

        $router->get('/contacts', function () {
            $this->capturedParams = ['called' => true];
        });

        ob_start();
        $router->dispatch('GET', '/api/v1/contacts');
        ob_end_clean();

        $this->assertTrue($this->capturedParams['called']);
    }
}
