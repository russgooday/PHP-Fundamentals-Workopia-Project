# Validation & Sanitization Layers

Notes from reviewing the `crud-updates` branch: what changed, why sanitizing at
storage time was wrong, and how the remaining layers (client-side, validation,
sanitization, mass-assignment protection) divide responsibility.

## The original bug

`FormRequest::sanitized()` used to run all fields through
`FILTER_SANITIZE_SPECIAL_CHARS` before storage. Two problems:

1. **`FILTER_FLAG_EMPTY_STRING_NULL` silently does nothing when paired with
   `FILTER_SANITIZE_SPECIAL_CHARS`** — that flag is only honoured by
   `FILTER_UNSAFE_RAW`/`FILTER_DEFAULT`. Empty strings stayed `''` instead of
   becoming `null`.
2. **HTML-escaping data before storing it is the wrong layer.** It corrupts
   the data permanently (`O'Connor` → `O&#39;Connor` in the DB) and only
   makes sense for one specific output context (HTML). Any other consumer —
   an API response, an export, a search index — inherits the mangled text.

**Rule: escape for output, at the point you render into a specific context
(HTML, JSON, etc). Never encode at input/storage time.**

## The four layers

Each layer answers a different question, about a different boundary. They
can look redundant when their field lists overlap (as they do for
`Listings` today) — that's coincidence, not duplication.

| Layer | Lives on | Question it answers | Security value |
|---|---|---|---|
| HTML5 (`required`, `pattern`, `type=email`) + JS | the view/browser | "Give the user instant feedback" | **None.** Trivially bypassed (disable JS, call the endpoint directly). UX only. |
| `Validator` / `$rules` | `FormRequest` | "Is this input shaped correctly, and does this endpoint even recognise these fields?" | Real gate. `Validator::validated()` uses `array_intersect_key($data, $rules)` — anything not in `$rules` is silently dropped, so it also acts as an *input-layer* allowlist. |
| `sanitize_overrides` / `stripAndNullify()` | `FormRequest` | "Turn raw HTTP strings into clean scalar values" (trim/strip control chars, `''` → `null`, `toFloat('5,000')` → `5000.0`) | Data-quality, not access control. |
| `$fillable` | `Model` (per subclass, e.g. `Listings`) | "Which columns on *this table* may ever be mass-written, regardless of caller?" | The real safety net — see below. |

## Why `$fillable` matters even though `$rules` already restricts the keys

`Model::create()` builds SQL column names and placeholders directly from
`array_keys($data)`. PDO can parameterize *values*, not *identifiers* —
column names have to be interpolated as text. Today that's safe only because
the one caller (`ListingsController::store()`) sources `$data` from
`FormRequest::sanitized()`, whose keys are already constrained to the
hardcoded `$rules` array.

That protection is **incidental to this one call path**. The moment
something calls `Model::create()` without going through a `FormRequest` —
a seeder, an admin script, a future API endpoint — `$rules`' filtering
doesn't exist and there's nothing stopping an unintended column from being
written.

`$fillable`, declared directly on `Model`/`Listings`, is what protects
`create()` on its own terms, independent of the caller.

```php
// App/Models/Listings.php
class Listings extends Model {
    protected string $table = 'listings';
    protected array $fillable = ['title', 'description', 'salary', /* ... */];
}
```

## Why `$fillable` must be an explicit list, not derived from the DB schema

Considered building `$fillable` (and even `$rules`) dynamically from
`INFORMATION_SCHEMA.COLUMNS`. Rejected for `$fillable` specifically:

Querying "all columns minus a manual exclusion list" (`NOT IN ('id',
'user_id', 'created_at')`) is **a blocklist wearing an allowlist's
clothes**. The entire point of a mass-assignment allowlist is that nothing
is writable unless explicitly opted in. A schema-derived list flips that:
*every new column becomes writable the moment it's migrated in*, unless
someone remembers to add it to the exclusion list.

Concretely: add a column like `is_featured` or `approved_by_admin` later,
and a schema-derived `$fillable` makes it instantly settable from a public
form — no code change required, no review trigger, nothing. This is the
same class of bug as historical Rails mass-assignment incidents (public
form fields able to set columns like `is_admin` because the framework's
default was "everything except an explicit blocklist").

**A security allowlist should only grow through an explicit, positive
action — never as a side effect of an unrelated schema change.** Manually
maintaining `$fillable` is a few field names and low effort; the payoff is
that it fails safe.

(Schema-derived *`$rules`* is lower-risk and could be a reasonable
convenience — worst case there is a UX validation bug, not a security
hole. Still wouldn't cover business rules like `salary`'s
`between:10000,1000000` or `email`'s format, so manual overrides are
still needed either way.)

## Why sanitization stays on `FormRequest`, not `Model`

Tempting to "cut out the middleman" and sanitize inside `Model::create()`
alongside `$fillable`. Rejected:

- **Different inputs.** `sanitize_overrides` assumes raw HTTP strings
  (`toFloat('5,000')`, `stripAndNullify(?string $value)`). `Model::create()`
  should be callable from anywhere — a seeder inserting `['salary' =>
  50000.0]` already has a clean typed value; running it through
  string-parsing logic would break or need defensive guards.
- **Different failure modes.** Sanitization is a *data-quality* concern.
  `$fillable` is a *data-access* concern. Merging them gives one method two
  unrelated reasons to change, making it easier to weaken one while editing
  the other.
- **Different lifetimes.** A model can be the target of multiple
  `FormRequest`s (create form, admin bulk import, CSV import) each with
  different sanitization needs for the same columns. `$fillable` stays
  constant regardless of source; sanitization doesn't.
