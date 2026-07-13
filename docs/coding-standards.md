# Coding Standards

This document outlines coding standards and patterns to follow when working on the Switch Scores codebase.

## Repository Pattern

**Rule: Keep Eloquent queries out of Controllers.**

All database queries using Eloquent or the Query Builder should be placed in Repository classes under `App\Domain\`, not in Controllers.

### Why?

- **Testability**: Repositories can be mocked in tests
- **Reusability**: Queries can be reused across multiple controllers
- **Maintainability**: Database logic is centralised and easier to update
- **Separation of concerns**: Controllers handle HTTP, repositories handle data

### Where to put repositories

Repositories live under `App\Domain\{Feature}\`:

```
App\Domain\Game\Repository.php              # Main game repository
App\Domain\Game\Repository\GameCrawlRepository.php    # Sub-repository for crawl queries
App\Domain\Game\Repository\GameStatsRepository.php    # Sub-repository for stats
App\Domain\Category\Repository.php          # Category repository
```

### Pattern to follow

**Bad - Eloquent in Controller:**
```php
// In Controller
public function show()
{
    $games = Game::where('last_crawl_status', 404)->active()->get();  // DON'T DO THIS
    return view('games.list', ['games' => $games]);
}
```

**Good - Using Repository:**
```php
// In Repository
class GameCrawlRepository
{
    public function getStatus404(): Collection
    {
        return Game::query()
            ->where('last_crawl_status', 404)
            ->active()
            ->orderBy('title')
            ->get();
    }
}

// In Controller
public function __construct(
    private GameCrawlRepository $repoGameCrawl,
) {}

public function show()
{
    $games = $this->repoGameCrawl->getStatus404();
    return view('games.list', ['games' => $games]);
}
```

### Exceptions

Simple `find()` calls are acceptable in Controllers when using route model binding or simple lookups:

```php
$game = $this->repoGame->find($gameId);  // OK - simple lookup via repository
```

## Dependency Injection

**Rule: Use constructor injection, not `new` instantiation.**

```php
// Good
public function __construct(
    private GameRepository $repoGame,
) {}

// Bad
$repo = new GameRepository();
```

## Enums

**Rule: Use Enums for status/type constants, not magic strings.**

Put enums in `App\Enums\`:

```php
// Good
use App\Enums\GameStatus;
$query->where('game_status', GameStatus::ACTIVE->value);

// Bad
$query->where('game_status', 'active');
```

## File Organisation

### Domain folder structure

`App\Domain\` should contain folders that map to models or features:

```
App\Domain\Game\           # Game-related logic
App\Domain\Category\       # Category-related logic
App\Domain\ReviewLink\     # Review link logic
```

### Retiring App\Services

`App\Services\` is deprecated. New code should go in `App\Domain\`. See task #114 for migration plan.

## Templates

### Twig, not Blade

This project uses Twig templates (via TwigBridge), not Laravel Blade.

- Templates are in `resources/views/` with `.twig` extension
- Use `{{ variable }}` for output
- Use `{% if %}`, `{% for %}` for control structures

### Calling methods in templates

When calling model methods in Twig, use parentheses:

```twig
{# Good #}
{% if game.isDelisted() %}

{# Bad - won't work #}
{% if game.isDelisted %}
```

## Migrations — foreign key column types

Core tables were created with `increments('id')` (unsigned INT), NOT `id()` (unsigned BIGINT).
When adding a foreign key that references them, use `$table->unsignedInteger('col')` — NOT
`unsignedBigInteger()` or `foreignId()`.

- Applies to FKs referencing `games.id` and `categories.id`.
- Using BIGINT causes `SQLSTATE[HY000] 3780` "Referencing column and referenced column are
  incompatible" at migration time (hit twice historically).

## Twig badge macros

- **Console badge** — `ui/components/game/console-badge.twig`: `consolebadge.standard(item)`
  ("Switch 1"/"Switch 2"), `consolebadge.short(item)` ("S1"/"S2"). Works with Eloquent
  models (`console.id`) and raw DB rows (`console_id`).
- **Low-quality badge** — staff/Bootstrap 5 pages use `gamelowquality.b5(item)`; public/
  Bootstrap 3 pages use `gamelowquality.standard(item)`. Using `.standard()` on a staff page
  renders unstyled text — a common mistake.

## UK Spelling

Use UK spelling in user-facing text:

- "Standardise" not "Standardize"
- "Categorisation" not "Categorization"
- "Colour" not "Color"
