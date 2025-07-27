# Refactor guide

This guide documents ongoing code improvements and refactoring efforts across the Switch Scores codebase.

The goal is to improve code consistency, maintainability, and performance without introducing breaking changes. Each section below describes a specific type of refactor, why it's being done, and how to do it.

---

## ✅ 1. Use constructor DI in commands

### Why:
- Easier to test and maintain
- Keeps logic outside the `handle()` method
- Better integration with Laravel’s container

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

## ✅ 2. Replace new with app() or constructor injection

### Why:
- Avoid hardcoded dependencies 
- Makes services swappable and testable 
- Leverages Laravel’s container

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

## ✅ 3. Move Code from app/Services to app/Domain

### Why:
- Services should live under a clearly structured domain layer 
- Aligns with project structure and naming conventions

### Action:
- Move each service class from app/Services/* to app/Domain/{Area}/*
- Update namespace and all references

## ✅ 4. Migrate Bootstrap 3 to Bootstrap 5

### Why:
- Bootstrap 3 is outdated and unsupported 
- Bootstrap 5 provides better layout and modern component support

### Notes:
- Replace .panel, .btn-default, .form-group with new Bootstrap 5 equivalents 
- Convert grid system classes: col-md-6 → col-md-6 still valid, but check .row wrappers 
- Drop all references to glyphicon-* (replace with icons like Font Awesome)

### Example:

```html
<!-- Before -->
<div class="panel panel-default">
  <div class="panel-heading">Title</div>
  <div class="panel-body">Content</div>
</div>

<!-- After -->
<div class="card mb-3">
  <div class="card-header">Title</div>
  <div class="card-body">Content</div>
</div>
```

## ✅ 5. Replace legacy breadcrumbs

### Why:
- Centralised breadcrumb management improves consistency 
- Avoids manually defining breadcrumbs in views

### Action:
- Move breadcrumbs into centralised configuration 
- Use helper functions or new classes where applicable

### Example (Top-level staff page)

```php
$pageTitle = 'Invite codes';
$breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
$bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);
```

## ✅ 6. Replace legacy view includes with Twig macros

### Why:
- Improves reusability of UI components 
- Makes views cleaner and easier to maintain

### Action:
- Migrate simple repeated view fragments to resources/views/macros/macros.twig 
- Create macros for buttons, form elements, info boxes, etc.

Before:
```twig
{% include 'components/button.twig' with { label: 'Submit' } %}
```

After:
```twig
{{ macros.button('Submit') }}
```

## ✅ 7. Improve caching of heavy queries

### Why:
- Reduce load on database for frequently accessed data 
- Improve performance for key pages

### Example:

```php
$games = Cache::remember('top_rated_games', 3600, function () {
    return Game::with('reviews')->orderBy('score', 'desc')->take(10)->get();
});
```

## ✅ 8. Enforce use of console IDs where needed

### Why:
- Ensures consistent and accurate filtering or linking 
- Avoids implicit assumptions and bugs from missing IDs

### Action:
- Identify places where console ID is required but missing 
- Add assertions or refactor data flows to ensure the ID is passed

