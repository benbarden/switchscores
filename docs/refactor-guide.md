# Refactor Guide

This guide documents ongoing code improvements and refactoring efforts across the Switch Scores codebase.

The goal is to improve code consistency, maintainability, and performance without introducing breaking changes.

**Status key:** `[Pending]` | `[In Progress]` | `[To review]` | `[Done]`

---

# Active / Pending Items

---

## Migrate Bootstrap 3 to Bootstrap 5: Public `[Pending]`

Migrate public site templates from Bootstrap 3 to Bootstrap 5. Do this before removing B3 classes from shared components.

See [Class Reference](#bootstrap-3--5-class-reference) below.

---

## Replace legacy breadcrumbs: Public `[Pending]`

Centralised breadcrumb management improves consistency and avoids manually defining breadcrumbs in views.

### Example (Top-level staff page):

```php
$pageTitle = 'Invite codes';
$breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
$bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);
```

---

## Heading macro refactor `[Pending]`

Replace `<h2>` tags with the heading macro for consistent styling:

```twig
{% import "ui/components/headings/heading.twig" as heading %}
{{ heading.renderSlick('Section Title') }}
```

---

## Upgrade Bootstrap 5.1 to 5.3: Staff `[Pending]`

Low risk minor version upgrade. Gives access to subtle color utilities (`bg-*-subtle`, `text-*-emphasis`), CSS custom properties, and dark mode support.

**Action:** Update CDN links in `resources/views/theme/staff-b5/base.twig` from `5.1.0` to `5.3.3`.

---

## Upgrade Bootstrap 5.1 to 5.3: Members `[Pending]`

As above, for member area templates.

---

## Upgrade Bootstrap 5.1 to 5.3: Public `[Pending]`

As above, for public site templates (after B3→B5 migration is complete).

---

## Use constructor DI in commands `[To review]`

Easier to test and maintain. Keeps logic outside the `handle()` method.

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

## Replace new with app() or constructor injection `[To review]`

Avoid hardcoded dependencies. Makes services swappable and testable.

### Before:
```php
$repo = new ConsoleRepository();
```

### After (preferred):
```php
public function __construct(private ConsoleRepository $repo) {}
```

### Fallback:
```php
$repo = app(ConsoleRepository::class);
```

---

## Consolidate App\Domain folder structure `[In Progress]`

Too many folders inside Domain, and not all map to real models. Consolidate related functionality under primary domain entities.

**Approach:**
- Collapse smaller domain folders into their parent entity
- Example: `App\Domain\GameLists\Repository.php` → `App\Domain\Game\Repository\GameListsRepository.php`

**Progress:**
- `App\Domain\GameStats\` moved into `App\Domain\Game\GameListRepository.php`

**Candidates for review:**
- `GameLists/` → `Game/`
- Other small folders that don't map to real models

---

## Tidy up Repository and DbQueries classes `[Pending]`

Some Repository classes are very long and could be split. Also, `DbQueries.php` files use raw DB statements instead of Eloquent ORM.

**Approach:**
- Split oversized Repository classes into focused sub-repositories
- Convert raw DB queries to Eloquent where practical
- Keep raw DB for genuinely complex queries where Eloquent would be unwieldy

---

## Move code from app/Services to app/Domain `[In Progress]`

Services should live under a clearly structured domain layer. Move each service class from `app/Services/*` to `app/Domain/{Area}/*` and update namespace and references.

**Status:** 13 files remaining:

- `DataSources/NintendoCoUk/` - UpdateGame, Importer, Parser, Images (4 files)
- `DataSources/Queries/Differences` (1 file)
- `Game/` - QualityScore, TitleMatch, Images (3 files)
- `DataQuality/QualityStats` (1 file)
- `Feed/` - TitleParser, Parser (2 files)
- `Eshop/US/Loader` (1 file)
- `EshopUSGameService` (1 file, root level)

---

## Improve caching of heavy queries `[To review]`

Reduce database load for frequently accessed data.

```php
$games = Cache::remember('top_rated_games', 3600, function () {
    return Game::with('reviews')->orderBy('score', 'desc')->take(10)->get();
});
```

---

## Enforce use of console IDs where needed `[To review]`

Ensures consistent filtering/linking. Identify places where console ID is required but missing and refactor data flows.

---

## Form refactoring `[To review]`

Cleaner form templates with less branching. Merge add/edit code with `old()` helper. Avoid branching with `formMode` where possible.

**Reference:** `staff/games/affiliates/edit.twig`

---

# Reference

## Bootstrap 3 → 5 Class Reference

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

**Notes:**
- When using rating badge macro, change `rating.score(..., 'b3')` to `rating.score(..., 'b5')`
- Bootstrap 5.3+ has `bg-*-subtle` classes for pastel colors; 5.1 requires inline styles

---

# Completed

---

## Migrate Bootstrap 3 to Bootstrap 5: Staff
**Completed:** January 2026

Migrated all staff section templates. See changelog for details.

---

## Replace legacy breadcrumbs: Staff
**Completed:** 2025

Centralised breadcrumb management for staff pages.

---

## Replace legacy breadcrumbs: Members
**Completed:** 2025

Centralised breadcrumb management for member pages.

---

## Replace legacy view includes with Twig macros: Staff
**Completed:** 2025

Migrated repeated view fragments to `resources/views/ui/components/`.

---

## Replace legacy view includes with Twig macros: Members
**Completed:** 2025

Migrated repeated view fragments to `resources/views/ui/components/`.

---

## Remove SwitchServices singleton
**Completed:** 2025

Removed the SwitchServices God class and replaced with controller dependency injection.
