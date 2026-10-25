<?php

use Framework\RouteCompiler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Spec: notes/route-validation.md
 *
 * Assumed API (rename freely):
 *
 *   RouteCompiler::compilePattern(string $path, array $patterns = [], array $defaults = []): string
 *     returns the full regex, delimiter # and the D modifier
 *
 * Invalid paths throw LogicException.
 *
 * Applying defaults to a match is the router's job, so the match test does
 * it in the matchUrl() helper at the bottom.
 */
class RouteCompilerTest extends TestCase {

    public static function regexCases(): array {
        return [
            '1 static path' => [
                '/listings', [], [],
                '#^/listings$#D',
            ],
            '2 default requirement' => [
                '/listings/{job_id}', [], [],
                '#^/listings/(?P<job_id>[^/]+)$#D',
            ],
            '3 user pattern' => [
                '/listings/{job_id}', ['job_id' => '\d+'], [],
                '#^/listings/(?P<job_id>\d+)$#D',
            ],
            '4 pattern on one of two variables' => [
                '/listings/{job_id}/edit/{other_id}', ['job_id' => '\d+'], [],
                '#^/listings/(?P<job_id>\d+)/edit/(?P<other_id>[^/]+)$#D',
            ],
            '5 dot separator' => [
                '/gallery/{image}.{format}', [], [],
                '#^/gallery/(?P<image>[^/.]+)\.(?P<format>[^/]+)$#D',
            ],
            '5b pattern lets image contain dots' => [
                '/gallery/{image}.{format}', ['image' => '[^/]+'], [],
                '#^/gallery/(?P<image>[^/]+)\.(?P<format>[^/]+)$#D',
            ],
            '6 optional trailing variable' => [
                '/gallery/{image}.{format}', [], ['format' => 'jpg'],
                '#^/gallery/(?P<image>[^/.]+)(?:\.(?P<format>[^/]+))?$#D',
            ],
            '7 hyphen separator' => [
                '/user-{id}', [], [],
                '#^/user\-(?P<id>[^/]+)$#D',
            ],
            '8 separator after the variable' => [
                '/{id}-user', [], [],
                '#^/(?P<id>[^/-]+)\-user$#D',
            ],
            '9 empty separator' => [
                '/user{id}', [], [],
                '#^/user(?P<id>[^/]+)$#D',
            ],
            '10 nested optionals' => [
                '/archive/{year}/{month}', [], ['year' => '2000', 'month' => '01'],
                '#^/archive(?:/(?P<year>[^/]+)(?:/(?P<month>[^/]+))?)?$#D',
            ],
            '11 trailing text breaks the optional run' => [
                '/{a}/edit', [], ['a' => 'home'],
                '#^/(?P<a>[^/]+)/edit$#D',
            ],
        ];
    }

    #[DataProvider('regexCases')]
    public function testRegex(string $path, array $patterns, array $defaults, string $regex): void {
        $this->assertSame($regex, RouteCompiler::compilePattern($path, $patterns, $defaults));
    }


    public static function matchCases(): array {
        $gallery = ['/gallery/{image}.{format}', []];
        $archive = ['/archive/{year}/{month}', [], ['year' => '2000', 'month' => '01']];

        return [
            '3 numeric'             => ['/listings/{job_id}', ['job_id' => '\d+'], [], '/listings/42',  ['job_id' => '42']],
            '3 non-numeric'         => ['/listings/{job_id}', ['job_id' => '\d+'], [], '/listings/abc', null],
            '4 two variables'       => ['/listings/{job_id}/edit/{other_id}', ['job_id' => '\d+'], [], '/listings/7/edit/cover', ['job_id' => '7', 'other_id' => 'cover']],

            '5 image.format'        => [...$gallery, [], '/gallery/cat.png', ['image' => 'cat', 'format' => 'png']],
            '5 missing format'      => [...$gallery, [], '/gallery/cat',     null],
            // The default only protects the dot that follows `image`; `format`
            // is [^/]+ so it swallows "final.png" (same as Symfony).
            '5 extra dot'           => [...$gallery, [], '/gallery/cat.final.png', ['image' => 'cat', 'format' => 'final.png']],
            // A user pattern replaces the default, so `image` may contain dots
            // and greedy backtracking splits on the last one.
            '5 image allows dots'   => ['/gallery/{image}.{format}', ['image' => '[^/]+'], [], '/gallery/cat.final.png', ['image' => 'cat.final', 'format' => 'png']],
            '5 restricted format'   => ['/gallery/{image}.{format}', ['format' => 'png|jpg'], [], '/gallery/cat.final.png', null],
            '5 restricted format ok' => ['/gallery/{image}.{format}', ['format' => 'png|jpg'], [], '/gallery/cat.png', ['image' => 'cat', 'format' => 'png']],

            '6 default format'      => ['/gallery/{image}.{format}', [], ['format' => 'jpg'], '/gallery/cat',     ['image' => 'cat', 'format' => 'jpg']],
            '6 explicit format'     => ['/gallery/{image}.{format}', [], ['format' => 'jpg'], '/gallery/cat.png', ['image' => 'cat', 'format' => 'png']],

            '7 hyphen'              => ['/user-{id}', [], [], '/user-5', ['id' => '5']],
            '7 no hyphen'           => ['/user-{id}', [], [], '/user5',  null],

            '8 id first'            => ['/{id}-user', [], [], '/5-user',   ['id' => '5']],
            '8 id contains hyphen'  => ['/{id}-user', [], [], '/5-6-user', null],

            '9 no separator'        => ['/user{id}', [], [], '/user123',  ['id' => '123']],
            '9 slash after prefix'  => ['/user{id}', [], [], '/user/123', null],

            '10 no params'          => [...$archive, '/archive',         ['year' => '2000', 'month' => '01']],
            '10 year only'          => [...$archive, '/archive/2026',    ['year' => '2026', 'month' => '01']],
            '10 both'               => [...$archive, '/archive/2026/10', ['year' => '2026', 'month' => '10']],

            '11 matches'            => ['/{a}/edit', [], ['a' => 'home'], '/x/edit', ['a' => 'x']],
            '11 not optional'       => ['/{a}/edit', [], ['a' => 'home'], '/edit',   null],
        ];
    }

    #[DataProvider('matchCases')]
    public function testMatch(string $path, array $patterns, array $defaults, string $url, ?array $expected): void {
        $this->assertEquals($expected, $this->matchUrl($path, $patterns, $defaults, $url));
    }


    public static function invalidPaths(): array {
        return [
            'duplicate name'        => ['/{a}/{a}', LogicException::class],
            'empty name'            => ['/{}',      LogicException::class],
            // '/{a' and '/a}': spec says pick reject OR literal. Add a test
            // here (reject) or in testMatch (literal) once you've decided.
        ];
    }

    #[DataProvider('invalidPaths')]
    public function testInvalidPathsAreRejected(string $path, string $exception): void {
        $this->expectException($exception);
        RouteCompiler::compilePattern($path);
    }


    /**
     * Stands in for the router: run the compiled regex, keep the named
     * groups that matched, and fall back to defaults for the rest.
     */
    private function matchUrl(string $path, array $patterns, array $defaults, string $url): ?array {
        $regex = RouteCompiler::compilePattern($path, $patterns, $defaults);

        if (!preg_match($regex, $url, $matches)) {
            return null;
        }

        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        return array_merge($defaults, $params);
    }
}
