<?php
namespace Spikes;

use PHPUnit\Framework\TestCase;
use Spikes\Fixtures\{Logger, Mailer, Database, ListingsController, UnresolvableClass};

require_once __DIR__ . '/Fixtures/ContainerFixtures.php';

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->register(Database::class, function () {
            return new Database('localhost', 'root', 'password', 'my_database');
        });
    }

    public function test_resolves_class_with_no_constructor_dependencies(): void
    {
        $logger = $this->container->resolve(Logger::class);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function test_resolves_class_with_a_single_dependency(): void
    {
        $mailer = $this->container->resolve(Mailer::class);
        $this->assertInstanceOf(Mailer::class, $mailer);
    }

    public function test_resolves_deeply_nested_dependencies(): void
    {
        $controller = $this->container->resolve(ListingsController::class);
        $this->assertInstanceOf(ListingsController::class, $controller);

        $this->expectOutputRegex('/rendered: listings\/index/');
        $controller->index();
    }

    public function test_registered_closure_is_used_instead_of_reflection(): void
    {
        $stub = new class extends Logger {
            public function log(string $message): void {
                echo "[STUB] {$message}\n";
            }
        };

        $this->container->register(Logger::class, fn() => $stub);

        $resolved = $this->container->resolve(Logger::class);
        $this->assertSame($stub, $resolved);
    }

    public function test_everything_is_cached_by_default(): void
    {
        $first = $this->container->resolve(Mailer::class);
        $second = $this->container->resolve(Mailer::class);

        $this->assertSame($first, $second);
    }

    public function test_no_constructor_class_is_cached_too(): void
    {
        $first = $this->container->resolve(Logger::class);
        $second = $this->container->resolve(Logger::class);

        $this->assertSame($first, $second);
    }

    public function test_registered_closure_is_cached_and_only_invoked_once(): void
    {
        $calls = 0;
        $this->container->register(Logger::class, function () use (&$calls) {
            $calls++;
            return new Logger();
        });

        $this->container->resolve(Logger::class);
        $this->container->resolve(Logger::class);

        $this->assertSame(1, $calls);
    }

    public function test_throws_invalid_argument_exception_when_constructor_params_are_builtins(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            "Unable to resolve Spikes\Fixtures\UnresolvableClass's constructor 'string' parameter 'host' "
        );
        $this->container->resolve(UnresolvableClass::class);
    }
}