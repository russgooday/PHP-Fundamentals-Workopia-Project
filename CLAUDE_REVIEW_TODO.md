# Workopia Framework Review: TODO

Scope: `src/` and `public/`. Reviewed 2026-10-04, then re-verified (see "Re-check log").
PHP 8.5.1 / Laragon. The review is read-only; nothing here has been applied except where marked **Done**.

**Verification key:** 🧪 reproduced against the running app · ▶ confirmed by running PHP · 📖 confirmed by reading code only (not executed)

**Assumptions:** auth is not built yet; Apache doc root is `public/`; `HomeController` and `Binding` were not reviewed in detail; production `php.ini` may differ from the CLI one used for tests.

## Summary
Hand-rolled MVC framework: autoloader, DI container with `afterResolving` hooks, URI/method router, dispatcher, PDO model, validator, and age-based flash messages. It serves CRUD job listings through PHP views.

---

## 🔴 Critical

- [x] **Reflected XSS in the create/edit form: DONE.** `create.view.php:15` printed `$job->id` unescaped, and `store()` re-renders with `(object) $request->post`, so a posted `id=">…` landed in the `action` attribute. 📖 on the original (`git show HEAD`), 🧪 on the patched version: now `e($job->id)` and the payload is escaped. Better still, build `$job` in `store()` without an `id` key.
- [x] **No CSRF protection: DONE.** Every POST/PUT/DELETE was forgeable, including Delete at `show.view.php:19`. 📖 Exploitable once auth exists. Also what made the XSS above cross-site reachable.
- [x] **Unknown HTTP methods give a 500: DONE.** `Request::method()` returned any `_method` value and `Router::match` read `$this->routes[$method]`, undefined for `PATCH`, `HEAD`, `OPTIONS`, `_method=foo`. Fixed without a second method list: `Router::match` returns `false` when `isset($this->routes[$method])` fails (`Router.php:87`), and `Request::method()` only honours `_method` on POST when it `is_string` (`Request.php:31`). The router's `$routes` keys are the single source of truth, so adding `PATCH` later means touching only the router. 🧪 Re-tested: `PATCH`, `OPTIONS` and `_method=foo` → 404. Note: `_method[]=x` still returns 500, but that now comes from `Validator::trimStrings` (see "Array input crashes the validator" below), not from method handling.
- [x] **Malformed URI gives a 500: DONE.** `routeMatch` now returns `null` when `parse_url` doesn't give a string (`Router.php:127`). 🧪 `GET //x` → 404.
- [x] **Array input crashes the validator: DONE.** Only reachable with a hand-crafted request; your forms never send arrays. `trimStrings` skips non-strings (`Validator.php`), the checks accept `mixed` and fail cleanly, and `FormRequest::sanitized()` only cleans strings and leaves arrays untouched (`FormRequest.php:50`). ▶ `ValidatorTest` covers the array cases, and a field declared `array:php,mysql` now passes through `validate()`, `validated()` and `sanitized()`. Parked for later: recursive cleaning of array contents, and passing the cleaner in as a callback.
- [x] **`GET /error/{status_code}` publicly routed to `ErrorController::index`: DONE.** The route is commented out in `routes.php:13`; `ErrorHandler` still calls the controller directly. 🧪 `GET /error/404` → a single 404 page.
- [x] **`stripAndNullify` removes newlines: DONE.** The regex is now `[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]` (`sql.php:55`), so `\t \n \r` survive and the other control characters are still stripped. ▶ `"line1\r\nline2"` is kept intact.
- [ ] **No authorization (assumption: auth is not built).** Anyone can edit or delete any listing; `update` overwrites `user_id` with 1.

## 🟡 Warning

- [x] **`logError` can fail inside the exception handler: DONE.** `logs/` is git-ignored, so a fresh clone won't have it. `logError` is now a private method of `ErrorHandler` that creates the folder if it is missing (`ErrorHandler.php:38`), with the path taken from `dirname(__DIR__, 2)` so the framework no longer calls into `App`. 🧪 With `logs/` removed, `GET /zyx` still renders the 404 page and recreates the log.
- [x] **Weak numeric validation: DONE.** `toFloat()` (`types.php:32`) now uses a regex (`^-?(\d{1,3}(,\d{3})*|\d+)(\.\d+)?$`) and `is_finite()`, so `12abc`, `-`, `1e3`, `NaN` and huge digit strings are rejected, `1,234.56` is accepted, and `null` is safe. No `intl` extension is needed for it any more. 🧪 Through the real form, only valid numbers pass the salary field, and the full suite is green.
- [x] **Database setup: DONE.** `charset=utf8mb4`, `ATTR_EMULATE_PREPARES => false`, and the `sql_mode` override removed (MySQL 8.4.3's own default is strict). 🧪 DB reads and the full suite are fine.
- [ ] **Session cookie not hardened.** No `HttpOnly` / `SameSite` / `Secure` / `use_strict_mode`. Do this before adding login. 📖
- [x] **`index` threw a 404 on an empty table: DONE.** `ListingsController::index()` now always renders the view, so the "no listings" message in `index.view.php` is reachable. 🧪 With an empty model, `index()` returns 200 and shows the message.
- [x] **`$errors` printed raw: DONE.** Now `e($error)` (`create.view.php:25`). 🧪 The errors page renders 200 with all messages.
- [x] **Exception path skipped `Session::storeNewMessages()`: DONE.** `index.php` now wraps `dispatch()` in `try { } finally { $session->storeNewMessages(); }`, so flash messages are aged out even when the request ends in an error. ▶ A leftover message is cleared after a 404.
- [ ] **Trace output in logs.** Retracted password concern (see log below), but the trace still includes the DB username and a truncated DSN. Set `zend.exception_ignore_args=On` in production (it is empty/Off in the CLI ini here).

## 🔵 Optimization / Style

- [x] `public/index.php` relative requires → `__DIR__ . '/../…'`: **Done**, but see the re-check log: my original "relies on the CWD" framing was overstated for Apache.
- [ ] `error_reporting(E_ALL)` runs at file scope in `ErrorHandler.php` (executes on autoload). Move it to the bootstrap and set `display_errors=0` in production (CLI ini here has it on).
- [x] The router regex was a method parameter: **DONE.** It is now `Router::PARAM_REGEX` (`Router.php:115`) and `routeMatch` takes two arguments.
- [ ] Column names in `create`/`update` are unquoted. They are whitelisted, but backtick them for reserved words.
- [x] `getMessage()` in `helpers/view.php` duplicated `Session::getMessage` and read `$_SESSION` directly: **DONE.** The helper is removed; the views use `$session->getMessage()`.
- [ ] `HttpErrorMessages` has no 401/405/422. Any other code becomes status 500 but keeps the custom message.
- [x] `messages.php` defines a `string` message, but `ValidatorChecks` had no `string` method: **DONE.** `ValidatorChecks::string()` exists; ▶ `required|string` on an array gives "must be a string".
- [ ] `.htaccess` hardcodes `/index.php` (breaks in a subdirectory); `[NC]` does nothing on `^`.
- [ ] `renderForEach` does `is_file` + `array_merge` per row; resolve the path once.

## 🟢 Checklist Passed

- Prepared statements with typed binds throughout, `$fillable` whitelist, and `validated()` limited to the rule keys.
- Output consistently escaped through `e()` (`ENT_QUOTES`, UTF-8).
- `.env` outside the web root; PDO in exception mode with a lazy connection.
- `_method` override restricted to POST; router kept minimal.
- Output buffering cleaned up when a view throws (including nested renders).
- Lazy curried error handler; `afterResolving` hooks apply to subclasses (`EventManager::on` uses `instanceof`).
- Validator rule ordering, `mb_strlen`, and a single `ViewNotFoundException` site.
- PDO password is masked in stack traces on PHP 8.5 (`SensitiveParameterValue`).

---

## Refactoring suggestions

**1. Unknown methods: DONE (applied by you; this is the version in the code)**

My first suggestion was a hardcoded whitelist in `Request` (`['PUT', 'PATCH', 'DELETE']`). It was dropped: `PATCH` isn't a method this app supports, and it would have duplicated the router's method list. What you wrote instead is better:
```php
// Request::method(): only POST can carry an override, and only as a string
$override = $this->post['_method'] ?? null;
if ($this->method === 'POST' && is_string($override)) {
    return $override;
}
return $this->method;

// Router::match(): an unknown method matches nothing, so the Dispatcher throws its 404
$method = strtoupper($method);
if (!isset($this->routes[$method])) {
    return false;
}
```
Trade-off accepted: a forged `_method=GET` on a POST is routed as a GET. GET routes have no side effects, so this is harmless. If it ever matters, add `Router::hasMethod()` rather than a second list. A proper 405 (URI exists, method not allowed) is a possible later addition but needs extra Router logic.

**2. URI path (Router::routeMatch)**
```php
$path = parse_url($uri, PHP_URL_PATH);
if (!is_string($path)) return null;
$uri = $this->_normalisePath($path);
// ...and decode per parameter, not the whole path (a decoded %2F would change the segment count):
$params[$matches[1]] = rawurldecode($uri_part);
```

**3. CSRF**
```php
// Session
public function csrfToken(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
// form
<input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">
// before dispatching any non-GET request
if (!hash_equals($session->csrfToken(), $request->post['_token'] ?? '')) {
    throw new HttpException(403);
}
```

**4. Keep newlines (sql.php)**
```php
// strip control chars but keep \t \n \r
'/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/'
```

**5. Array-safe trim (Validator::trimStrings)**
```php
$trimmed[$key] = is_string($value) ? trim($value) : '';
```

**6. PDO options (Database)**
```php
"mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4"   // add a $port ctor param
[
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES   => false,
]
```
Drop the init command, or append `STRICT_TRANS_TABLES` to the server mode rather than replacing it.

**7. Safe logging (`logError`)**
```php
$dir = dirname($errorLog);
is_dir($dir) || @mkdir($dir, 0775, true);
@error_log("[{$timestamp}] …", 3, $errorLog);   // the logger must never throw
```

**9. Error route.** Remove `->get('/error/{status_code}', 'ErrorController')` from `routes.php`. Only the exception handler should call `ErrorController`.

**Suggested order:** ~~1~~ done → 2, 4, 5 (real bugs in the normal flow, each a few lines) → 9 → ~~3~~ (CSRF) done → 6/7 → the rest.

---

## Re-check log (corrections to the first review)

| Item | First review said | After re-checking |
|---|---|---|
| `__DIR__` in `index.php` | "Relies on the CWD; use `__DIR__ . '/../src/…'`" (snippet was missing the leading `/`) | Overstated. Under Apache the CWD is `public/`, so the relative path worked. It only matters for the built-in server or CLI. Now applied correctly, but it was never a defect. |
| Secrets in logs | PDO trace logs the password (short passwords in full) | **Wrong on PHP 8.5.** The password is masked as `SensitiveParameterValue`. Only the username and a truncated DSN appear. Demoted to a minor note. |
| XSS at `create.view.php:15` | Critical | Correct for the code as I read it (`git show HEAD` confirms unescaped). You have since patched it; the live test shows it escaped. |
| `/error/404` | "Renders twice and the visitor sees a 500" | Confirmed live: status 500, body rendered twice. |
| URI-decoding snippet | Decode the whole path | Contradicted my own note. A decoded `%2F` changes the segment count, so decode per parameter. Fixed in snippet 2. |
| Method override fix | Hardcoded whitelist `['PUT', 'PATCH', 'DELETE']` in `Request` | `PATCH` isn't a supported method, and a second list would drift from the router's. Replaced by the router guard plus `is_string` (item 1, done). I also wrongly said the array case wasn't covered after your `is_string` check; it was. |
| `_method[]=x` after the fix | n/a | Still 500 on re-test, but caused by `Validator::trimStrings`, not by method handling. Now fixed by the `is_string` skip in `trimStrings`. |
| One shared `Request` | n/a | The Dispatcher's `Request` and the one injected into `FormRequest` were two different objects (`Request` had no binding). Fixed by `->singleton(Request::class, fn() => new Request())` in `Services.php` and `$dispatcher->dispatch($container[Request::class])` in `index.php`. ▶ Verified: same instance everywhere. |
| `unset($this->post['_method'])` idea | n/a | Considered and dropped: it made `method()` stateful (a second call would return `'POST'`) and the raw value is useful to keep for logging. Handled by the router `isset` guard plus `is_string` in `Request` instead. |
| Unclaimed before | n/a | Added: `string` message with no matching check, and the "`castTo` gets `?int` as a string" detail (▶ verified). |

**Test side effects:** the live probes ran PHP's built-in server on ports 8089/8090 (both stopped) and appended error entries to the git-ignored `logs/error.log`. No listings or other data were written, because every POST failed validation before reaching the database.
