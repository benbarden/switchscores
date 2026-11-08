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

**Rule: a folder in `App\Domain\` is a model or a feature.**

```
App\Domain\Game\           # The Game model: repositories, packshots, quality filter
App\Domain\Category\       # The Category model
App\Domain\GameImport\     # A feature: importing games
App\Domain\Sitemap\        # A feature: building the sitemaps
```

- A folder about its own model stays separate, even if its name starts with `Game` (`GameSeries`, `GameTag`).
- A folder that is really a set of queries over `games` belongs under `Game\Repository\` (for example game stats and game lists), not in a folder of its own.
- Don't fold everything into `Game\` either: it would become the next oversized folder.

### Class roles and names

One name per role:

- **`Repository`** - reads and writes for a model, using Eloquent, with caching through `AbstractRepository`.
- **`DbQueries`** - only for queries that need the query builder or raw SQL rather than Eloquent, usually for performance (large result sets, aggregates, joins Eloquent makes slow). Don't mix the two styles in one file: Eloquent methods go in the `Repository`, query-builder methods in `DbQueries`.
- **A role name for classes that do work** - `PackshotWriter`, `PriceLookup`, `TitleNormaliser`. Not `Repository`, not `Service`.
- No other names for the same roles (`Repo`, `Queries`, `Helper`); rename them when the file is touched.

**Prefer Eloquent.** Use the query builder only when it clearly helps, and then return what the callers need. Avoid query-builder code that fetches ids and then loads models by hand, or builds collections that Eloquent would have returned anyway. A big loop can be faster with the query builder, but if every caller expects Eloquent models, keep returning models.

### Repository size

**Split repositories by the question they answer, not by size.** Sub-repositories go in a `Repository\` folder next to the main one:

```
App\Domain\Game\Repository.php                          # Core lookups: find, search by title
App\Domain\Game\Repository\GameCrawlRepository.php      # The crawl queue and crawl status
App\Domain\Game\Repository\GameStatsRepository.php      # Counts and totals
App\Domain\Game\Repository\GameImageRepository.php      # Packshot rows
```

As a signal, not a hard limit: a repository past about 250 lines or 15 public methods is doing more than one job, and its next new method should start a sub-repository rather than add to it.

### Retiring App\Services

`App\Services\` is deprecated and is being emptied into `App\Domain\`. Never add a class to it. When a change substantially rewrites a class in it, move the class to the right `App\Domain\` folder as part of that change.

## Improving as we go

Every change should leave the structure no worse, and a bit better where it's cheap.

- **Never make it worse:** no new classes in `App\Services\`, no queries in controllers, and no new methods in a repository that's already past the size signal - put them in the right sub-repository, creating it if it's the first.
- **Improve what you touch, within a budget:** when a change edits methods in an oversized repository, move those methods (and close neighbours) to the right sub-repository if it's cheap. Rule of thumb: no more refactoring than the size of the change itself. Anything bigger is its own change.
- **Moves with many callers are always their own change** (for example the main `Game\Repository`), never folded into a feature.
- **Refactor alongside planned work:** when a feature is going to rework an area anyway, that's the time to restructure it.

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
