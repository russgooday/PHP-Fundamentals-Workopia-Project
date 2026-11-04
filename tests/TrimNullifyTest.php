<?php

use App\Middleware\TrimNullify;
use Framework\Request;
use Framework\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;

class TrimNullifyTest extends TestCase {

    private function process(array $post): array {
        $request = new Request('/', 'POST', [], $post, [], [], []);
        $next = $this->createStub(RequestHandlerInterface::class);

        (new TrimNullify())->process($request, $next);

        return $request->post;
    }

    public function testTrimsStrings(): void {
        $this->assertSame(['title' => 'hello'], $this->process(['title' => '  hello  ']));
    }

    public function testWhitespaceOnlyBecomesNull(): void {
        $this->assertSame(['title' => null], $this->process(['title' => '   ']));
    }
}
