Here's a spec you can turn into tests. Assumptions:

- Separator set is `/ . -`.
- Paths are normalised to start with `/`.
- Default requirement is `[^/<next separator>]+`, where the next separator is the first real character after the placeholder, if it's in the set and isn't `/`.
- A default makes a variable optional only if it's in the trailing run of variable tokens.

Token shape is `['text', str]` or `['variable', separator, requirement, name]`.

## Cases

| # | Path | Patterns / defaults | Tokens | Regex |
|---|---|---|---|---|
| 1 | `/listings` | none | `[text '/listings']` | `#^/listings$#D` |
| 2 | `/listings/{job_id}` | none | `[text '/listings']`<br>`[var '/', '[^/]+', job_id]` | `#^/listings/(?P<job_id>[^/]+)$#D` |
| 3 | `/listings/{job_id}` | `job_id => \d+` | `[text '/listings']`<br>`[var '/', '\d+', job_id]` | `#^/listings/(?P<job_id>\d+)$#D` |
| 4 | `/listings/{job_id}/edit/{other_id}` | `job_id => \d+` | `[text '/listings']`<br>`[var '/', '\d+', job_id]`<br>`[text '/edit']`<br>`[var '/', '[^/]+', other_id]` | `#^/listings/(?P<job_id>\d+)/edit/(?P<other_id>[^/]+)$#D` |
| 5 | `/gallery/{image}.{format}` | none | `[text '/gallery']`<br>`[var '/', '[^/.]+', image]`<br>`[var '.', '[^/]+', format]` | `#^/gallery/(?P<image>[^/.]+)\.(?P<format>[^/]+)$#D` |
| 6 | `/gallery/{image}.{format}` | `format` default `jpg` | same tokens as 5 | `#^/gallery/(?P<image>[^/.]+)(?:\.(?P<format>[^/]+))?$#D` |
| 7 | `/user-{id}` | none | `[text '/user']`<br>`[var '-', '[^/]+', id]` | `#^/user\-(?P<id>[^/]+)$#D` |
| 8 | `/{id}-user` | none | `[var '/', '[^/\-]+', id]`<br>`[text '-user']` | `#^/(?P<id>[^/\-]+)\-user$#D` |
| 9 | `/user{id}` | none | `[text '/user']`<br>`[var '', '[^/]+', id]` | `#^/user(?P<id>[^/]+)$#D` |
| 10 | `/archive/{year}/{month}` | both have defaults | `[text '/archive']`<br>`[var '/', '[^/]+', year]`<br>`[var '/', '[^/]+', month]` | `#^/archive(?:/(?P<year>[^/]+)(?:/(?P<month>[^/]+))?)?$#D` |
| 11 | `/{a}/edit` | `a` has a default | `[var '/', '[^/]+', a]`<br>`[text '/edit']` | `#^/(?P<a>[^/]+)/edit$#D`. Not optional, because the trailing text breaks the run. |

## Expected matches

| Case | URL | Result |
|---|---|---|
| 3 | `/listings/42` | `job_id=42` |
| 3 | `/listings/abc` | no match |
| 4 | `/listings/7/edit/cover` | `job_id=7`, `other_id=cover` |
| 5 | `/gallery/cat.png` | `image=cat`, `format=png` |
| 5 | `/gallery/cat` | no match |
| 5 | `/gallery/cat.final.png` | no match, because `image` excludes `.` |
| 6 | `/gallery/cat` | `image=cat`, `format` missing, so apply the default `jpg` |
| 6 | `/gallery/cat.png` | `image=cat`, `format=png` |
| 7 | `/user-5` | `id=5` |
| 7 | `/user5` | no match |
| 8 | `/5-user` | `id=5` |
| 8 | `/5-6-user` | no match, because `id` excludes `-` |
| 9 | `/user123` | `id=123` |
| 9 | `/user/123` | no match |
| 10 | `/archive` | defaults for both |
| 10 | `/archive/2026` | `year=2026`, default `month` |
| 10 | `/archive/2026/10` | both captured |
| 11 | `/x/edit` | `a=x` |
| 11 | `/edit` | no match |

## Compile-time errors to test

| Path | Expected |
|---|---|
| `/{a}{b}` | Reject: adjacent placeholders are ambiguous. |
| `/{a}/{a}` | Reject: duplicate variable name. |
| `/{a` or `/a}` | Either reject, or treat the stray brace as literal text. Pick one. |
| `/{}` | Reject: empty name. |

## Notes

- Applying defaults to the match result is the router's job, not the regex's. Make missing optional groups fall back to the default.
- A user-supplied pattern bypasses the next-separator exclusion, as in cases 3 and 4.
- In the regexes above, `\-` is what `preg_quote($str, '#')` produces for `-`, so expect that in your output.
- Case 10's nesting is the fiddliest part. If you'd rather keep it simple, you could allow only one optional trailing variable.
