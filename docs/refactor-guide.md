# Refactor Guide

This guide documents ongoing code improvements and refactoring efforts across the Switch Scores codebase.

The goal is to improve code consistency, maintainability, and performance without introducing breaking changes. Each section describes a specific type of refactor, why it's being done, and how to do it.

**Status key:** `[Done]` | `[In Progress]` | `[Pending]`

---

## 1. Use constructor DI in commands `[Done]`

### Why:
- Easier to test and maintain
- Keeps logic outside the `handle()` method
- Better integration with Laravel's container

### Before:
```php
public function handle()
{
    $service = new SomeService();
    $service->run();
}
```

### After:
```php
public function __construct(private SomeService $service) {}

public function handle()
{
    $this->service->run();
}
```

---

## 2. Replace new with app() or constructor injection `[Done]`

### Why:
- Avoid hardcoded dependencies
- Makes services swappable and testable
- Leverages Laravel's container

### Before:
```php
$repo = new ConsoleRepository();
```

### After:
Prefer constructor injection:

```php
public function __construct(private ConsoleRepository $repo) {}
```

Fallback (if not in a class context):

```php
$repo = app(ConsoleRepository::class);
```

---

## 3. Move code from app/Services to app/Domain `[Done]`

### Why:
- Services should live under a clearly structured domain layer
- Aligns with project structure and naming conventions

### Action:
- Move each service class from `app/Services/*` to `app/Domain/{Area}/*`
- Update namespace and all references

---

## 4. Migrate Bootstrap 3 to Bootstrap 5 `[In Progress]`

### Why:
- Bootstrap 3 is outdated and unsupported
- Bootstrap 5 provides better layout and modern component support

### Status:
- **Staff section:** Done (January 2026)
- **Public section:** Pending - do this before removing B3 classes from shared components

### Class reference (B3 → B5):

| Bootstrap 3 | Bootstrap 5 | Notes |
|-------------|-------------|-------|
| `form-horizontal` | (remove) | Not needed in B5 |
| `form-group` | `row mb-3` | Row with margin-bottom |
| `control-label` | `col-form-label` | Label styling |
| `form-control` on `<select>` | `form-select` | Select-specific styling |
| `label label-*` | `badge bg-*` | e.g. `label-danger` → `bg-danger` |
| `table-condensed` | `table-sm` | Compact tables |
| `btn-xs` | `btn-sm` | Small buttons |
| `btn-default` | `btn-outline-secondary` | Default button style |
| `text-right` | `text-end` | RTL-friendly naming |
| `text-left` | `text-start` | RTL-friendly naming |
| `pull-right` | `float-end` | RTL-friendly naming |
| `pull-left` | `float-start` | RTL-friendly naming |
| `col-xs-*` | `col-*` | Extra-small breakpoint |
| `col-md-offset-*` | `offset-md-*` | Column offsets |
| `img-responsive` | `img-fluid` | Responsive images |
| `panel` | `card` | Panel replacement |

### Notes:
- When using rating badge macro, change `rating.score(..., 'b3')` to `rating.score(..., 'b5')`
- Bootstrap 5.3+ has `bg-*-subtle` classes for pastel colors; 5.1 requires inline styles

---

## 5. Replace legacy breadcrumbs `[Done]`

### Why:
- Centralised breadcrumb management improves consistency
- Avoids manually defining breadcrumbs in views

### Action:
- Move breadcrumbs into centralised configuration
- Use helper functions or new classes where applicable

### Example (Top-level staff page):

```php
$pageTitle = 'Invite codes';
$breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
$bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);
```

---

## 6. Replace legacy view includes with Twig macros `[In Progress]`

### Why:
- Improves reusability of UI components
- Makes views cleaner and easier to maintain

### Action:
- Migrate simple repeated view fragments to `resources/views/macros/` or `resources/views/ui/components/`
- Create macros for buttons, form elements, info boxes, etc.

### Before:
```twig
{% include 'components/button.twig' with { label: 'Submit' } %}
```

### After:
```twig
{{ macros.button('Submit') }}
```

### Pending: Heading macro refactor
Replace `<h2>` tags with the heading macro:
```twig
{% import "ui/components/headings/heading.twig" as heading %}
{{ heading.renderSlick('Section Title') }}
```

---

## 7. Improve caching of heavy queries `[Done]`

### Why:
- Reduce load on database for frequently accessed data
- Improve performance for key pages

### Example:

```php
$games = Cache::remember('top_rated_games', 3600, function () {
    return Game::with('reviews')->orderBy('score', 'desc')->take(10)->get();
});
```

---

## 8. Enforce use of console IDs where needed `[Done]`

### Why:
- Ensures consistent and accurate filtering or linking
- Avoids implicit assumptions and bugs from missing IDs

### Action:
- Identify places where console ID is required but missing
- Add assertions or refactor data flows to ensure the ID is passed

---

## 9. Form refactoring `[In Progress]`

### Why:
- Cleaner form templates with less branching
- Better use of Laravel's `old()` helper for form repopulation

### Pattern:
- Merge add/edit code with `old()` helper
- Avoid branching with `formMode` where possible

### Reference files:
- `staff/games/affiliates/edit.twig` (updated)

### To update:
- Other staff forms as encountered

---

## 10. Upgrade Bootstrap 5.1 to 5.3 `[Pending]`

### Why:
- Access to subtle color utilities (`bg-*-subtle`, `text-*-emphasis`)
- CSS custom properties throughout
- Dark mode support (opt-in)
- Low risk - minor version upgrade

### Action:
- Update CDN links in `resources/views/theme/staff-b5/base.twig`
- Change `5.1.0` to `5.3.3`
- Test main staff pages
