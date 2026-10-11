<?php
namespace Tests;

require_once __DIR__ . '/Fixtures/ContainerFixtures.php';

use PHPUnit\Framework\TestCase;
use Fixtures\{
    Logger,
    Mailer,
    Database,
    ListingsController,
    UnresolvableClass,
    Cache,
    FileCache,
    ArgsController
};

use Framework\Container\Container;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();

        // Database has builtin (string) constructor params, so it can't be autowired
        $this->container->bind(Database::class, function () {
            return new Database('localhost', 'root', 'password', 'my_database');
        });
    }

    // Autowiring

    public function test_resolves_class_with_no_constructor_dependencies(): void
    {
        $logger = $this->container->get(Logger::class);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function test_resolves_class_with_a_single_dependency(): void
    {
        $mailer = $this->container->get(Mailer::class);
        $this->assertInstanceOf(Mailer::class, $mailer);
    }

    public function test_resolves_deeply_nested_dependencies(): void
    {
        $controller = $this->container->get(ListingsController::class);
        $this->assertInstanceOf(ListingsController::class, $controller);

        $this->expectOutputRegex('/rendered: listings\/index/');
        $controller->index();
    }

    public function test_autowired_classes_are_not_cached(): void
    {
        $first = $this->container->get(Mailer::class);
        $second = $this->container->get(Mailer::class);

        $this->assertNotSame($first, $second);
    }

    public function test_throws_when_constructor_params_are_builtins(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            "Unable to resolve Fixtures\UnresolvableClass's constructor parameter 'host' without a resolvable type."
        );
        $this->container->get(UnresolvableClass::class);
    }

    public function test_throws_when_interface_has_no_binding(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Fixtures\Cache is not instantiable and has no binding.');
        $this->container->get(Cache::class);
    }

    // Bindings

    public function test_binding_is_used_instead_of_reflection(): void
    {
        $stub = new class extends Logger {
            public function log(string $message): void {
                echo "[STUB] {$message}\n";
            }
        };

        $this->container->bind(Logger::class, fn() => $stub);

        $this->assertSame($stub, $this->container->get(Logger::class));
    }

    public function test_binding_can_map_an_interface_to_a_concrete_class(): void
    {
        $this->container->bind(Cache::class, fn() => new FileCache());

        $this->assertInstanceOf(FileCache::class, $this->container->get(Cache::class));
    }

    public function test_binding_closure_receives_the_container(): void
    {
        $received = null;
        $this->container->bind(Logger::class, function ($container) use (&$received) {
            $received = $container;
            return new Logger();
        });

        $this->container->get(Logger::class);

        $this->assertSame($this->container, $received);
    }

    public function test_bind_invokes_the_closure_on_every_resolve(): void
    {
        $calls = 0;
        $this->container->bind(Logger::class, function () use (&$calls) {
            $calls++;
            return new Logger();
        });

        $first = $this->container->get(Logger::class);
        $second = $this->container->get(Logger::class);

        $this->assertSame(2, $calls);
        $this->assertNotSame($first, $second);
    }

    public function test_singleton_is_cached_and_only_invoked_once(): void
    {
        $calls = 0;
        $this->container->singleton(Logger::class, function () use (&$calls) {
            $calls++;
            return new Logger();
        });

        $first = $this->container->get(Logger::class);
        $second = $this->container->get(Logger::class);

        $this->assertSame(1, $calls);
        $this->assertSame($first, $second);
    }

    public function test_singleton_is_shared_with_autowired_dependents(): void
    {
        $this->container->singleton(Logger::class, fn() => new Logger());
        $logger = $this->container->get(Logger::class);
        $mailer = $this->container->get(Mailer::class);

        $this->assertSame($logger, $mailer->logger);
    }

    // afterResolving hooks

    public function test_after_resolving_handler_receives_instance_and_container(): void
    {
        $received = [];
        $this->container->afterResolving(Logger::class, function ($logger, $container) use (&$received) {
            $received = [$logger, $container];
        });

        $logger = $this->container->get(Logger::class);

        $this->assertSame([$logger, $this->container], $received);
    }

    public function test_after_resolving_matches_subclasses_and_interfaces(): void
    {
        $calls = 0;
        $this->container->afterResolving(Cache::class, function () use (&$calls) {
            $calls++;
        });
        $this->container->bind(Cache::class, fn() => new FileCache());

        $this->container->get(Cache::class);
        $this->container->get(FileCache::class);

        $this->assertSame(2, $calls);
    }

    public function test_after_resolving_ignores_unrelated_types(): void
    {
        $calls = 0;
        $this->container->afterResolving(Mailer::class, function () use (&$calls) {
            $calls++;
        });

        $this->container->get(Logger::class);

        $this->assertSame(0, $calls);
    }

    public function test_after_resolving_does_not_run_again_for_a_cached_singleton(): void
    {
        $calls = 0;
        $this->container->singleton(Logger::class, fn() => new Logger());
        $this->container->afterResolving(Logger::class, function () use (&$calls) {
            $calls++;
        });

        $this->container->get(Logger::class);
        $this->container->get(Logger::class);

        $this->assertSame(1, $calls);
    }

    // ArrayAccess and has()

    public function test_array_access_resolves_and_binds(): void
    {
        $this->container[Cache::class] = fn() => new FileCache();

        $this->assertTrue(isset($this->container[Cache::class]));
        $this->assertInstanceOf(FileCache::class, $this->container[Cache::class]);
    }

    public function test_has_only_reports_bindings_and_cached_instances(): void
    {
        $this->assertTrue($this->container->has(Database::class));
        $this->assertFalse($this->container->has(Logger::class)); // autowirable, but not registered
    }

    public function test_unset_removes_binding_and_cached_instance(): void
    {
        $this->container->singleton(Logger::class, fn() => new Logger());
        $this->container->get(Logger::class);

        unset($this->container[Logger::class]);

        $this->assertFalse($this->container->has(Logger::class));
    }

    // getMethodArguments()

    public function test_method_arguments_are_mapped_from_route_params_and_container(): void
    {
        $args = $this->container->getMethodArguments(
            new ArgsController(), 'show', ['job_id' => '42', 'sort' => 'desc']
        );

        $this->assertSame(42, $args[0]);                    // route param cast to int
        $this->assertInstanceOf(Logger::class, $args[1]);   // class resolved from the container
        $this->assertSame('desc', $args[2]);                // route param overrides the default
        $this->assertNull($args[3]);                        // default value used
    }

    public function test_method_arguments_use_defaults_when_route_param_missing(): void
    {
        $args = $this->container->getMethodArguments(new ArgsController(), 'show', ['job_id' => '1']);

        $this->assertSame('asc', $args[2]);
    }

    public function test_method_arguments_pass_null_for_nullable_without_default(): void
    {
        $args = $this->container->getMethodArguments(new ArgsController(), 'nullable', []);

        $this->assertSame([null], $args);
    }

    public function test_method_arguments_throw_when_required_param_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs("Missing required parameter 'id'.");

        $this->container->getMethodArguments(new ArgsController(), 'needsId', []);
    }
}
