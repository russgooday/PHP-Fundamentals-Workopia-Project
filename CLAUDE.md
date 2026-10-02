# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

"Workopia" is a job-listings app following Brad Traversy's *PHP From Scratch* course, but built on a hand-rolled, Laravel-like framework (`src/Framework`) rather than a vendor framework. It is a learning project: the author writes the code, so discuss and advise rather than implementing unless explicitly asked.

## Commands

- Runs under Laragon (Apache, document root `public/`, `.htaccess` front controller). DB credentials come from `.env` (parsed with `parse_ini_file`, spread into `Database`). Schema: `database/listings.sql`.
- Run tests: `vendor/bin/phpunit` (config in `phpunit.xml`, bootstrap `tests/bootstrap.php`).
- Single test: `vendor/bin/phpunit --filter RouterTest` or `vendor/bin/phpunit tests/RouterTest.php`.
- No linter or build step. Composer is used only for PHPUnit (dev); classes are NOT loaded via Composer's autoloader.

## Architecture

**Autoloading:** a custom PSR-4 `Autoloader` (`autoloader.php`) is registered in `public/index.php` and `tests/bootstrap.php` (the latter adds a `Spikes\` namespace). Namespaces `App\` -> `src/App/`, `Framework\` -> `src/Framework/`. Global helper functions are pulled in by `src/App/functions.php` (the `helpers/` and `support/` files: array, function/currying, string, sql, types, session, debug, logging, view).
**Request lifecycle** (`public/index.php`):
1. `App\Config\Services::register()` populates the `Framework\Container\Container`.
2. Error/exception handlers are set lazily (curried) so `ErrorHandler` is only resolved from the container when needed.
3. `App\Config\Routes::register()` builds the `Router` (uri + method matching only; keep it minimal, request interpretation such as the `_method` override belongs on `Request`).
4. `Session` is started, `Dispatcher::dispatch(Request)` matches the route, resolves the controller (namespace `App\Controllers\`) from the container, maps route params onto action method arguments via reflection, and returns a `Response`.
5. `Session::storeNewMessages()` runs, then `Response::send()`.

**Container** (`src/Framework/Container/`): autowiring DI container with `singleton`/binding support, array access (`$container[Foo::class]`), and Laravel-style `afterResolving(Type, fn($obj, $container))` hooks (`EventManager`). `Services.php` uses these hooks for uniform post-construction wiring: controllers get `setResponse`/`setViewer`, and every `ViewerInterface` gets a shared `session` variable (the `Session` itself). Design notes are in `notes/`.

**Controllers** extend `Framework\Controller`, which exposes `view()`, `redirect()`, `addHeader()` returning a `Response`. Routes are declared as `'ControllerName@action'` strings in `src/App/Config/routes.php` (no `@` means the default action, e.g. `index`). Views live in `src/App/views/` (`*.view.php`, partials in `views/partials/`) and are rendered by `PHPViewer` behind `ViewerInterface`. Sanitize at view render time, not at storage.

**Models / validation:** `Framework\Model` wraps PDO via `Database`. `FormRequest` (e.g. `App\FormRequests\ListingsFormRequest`) uses `Validation\Validator`, with `ValidatorChecks` and `MessageLoader` (default messages in `Validation/messages.php`).

**Sessions / flash messages:** age-based (not access-based) flash messages in `Session`; new messages are stored at the end of the request and shared into views as the `session` variable (the `Session` object is passed directly, no proxy).

**Errors:** `ErrorHandler` and `HttpException` handle failures; the error path is deliberately separate from the normal `Response` flow. Do not add view-existence checks: missing view files should bubble up to the global handler.

## Conventions

- Prefer composition over inheritance: construct internal helpers (e.g. `ValidatorChecks`) inside the constructor rather than extending them or exposing them as constructor params.
- Prefer an associative array lookup with `??` over `match` for simple value mappings.
- Avoid `filter_var`/`FILTER_*` for sanitizing; use plain functions or closures.
- Goal: eventually support HTMX conventions (`HX-Request`, `HX-Redirect`) as first-class framework features, not one-off hacks.

## Non-source directories

`dev/`, `old_files/`, `logs/`, and `resources/` are git-ignored scratch/reference material (experiments, theme assets). `tests/Spikes/` holds exploratory Container tests.
