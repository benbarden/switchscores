# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Switch Scores is a Laravel 10 application for tracking Nintendo Switch game reviews and ratings. It aggregates review scores from multiple sources, maintains game databases, and provides public/staff/member interfaces.

## Common Commands

```bash
# Run fast tests (Unit + Feature)
make test

# Run slow page tests (browser-based)
make test-page

# Run all tests
make test-all

# Run specific test by filter
make test-filter F=testMethodName

# Run specific test file
make test-file FILE=tests/Unit/SomeTest.php

# Laravel artisan
php artisan <command>

# Asset compilation
npm run dev          # Development build
npm run watch        # Watch mode
npm run prod         # Production build
```

## Architecture

### Domain-Driven Structure

Business logic lives in `app/Domain/` organized by feature area:
- `Game/` - Core game queries, quality filtering, repositories
- `GameLists/` - Game list queries and filtering
- `ReviewLink/` - Review aggregation logic
- `View/` - Page builders and breadcrumbs
- `TopRated/` - Ranking calculations
- `Scraper/` - External data fetching

### View Layer

Uses **Twig templates** (via TwigBridge) instead of Blade:
- Templates in `resources/views/` with `.twig` extension
- Macros in `resources/views/macros/`
- Theme layouts in `resources/views/theme/`

Page building uses a builder pattern:
- `App\Domain\View\PageBuilders\` - StaffPageBuilder, MembersPageBuilder, PublicPageBuilder
- `App\Domain\View\Breadcrumbs\` - Centralized breadcrumb management

### Route Organization

Routes are split by user type:
- `routes/web.php` - Auth routes
- `routes/public.php` - Public site pages
- `routes/staff/` - Staff admin pages
- `routes/members/` - Member area pages
- `routes/api.php` - API endpoints

### Controller Structure

Controllers in `app/Http/Controllers/` organized by area:
- `PublicSite/` - Public-facing pages
- `Staff/` - Admin functionality
- `Members/` - Logged-in member features
- `Api/` - API endpoints
- `Owner/` - Site owner functions

### Models

Eloquent models in `app/Models/`. Key models:
- `Game` - Central entity with relationships to categories, tags, reviews
- `Console` - Switch 1/2 differentiation
- `ReviewLink` - Individual review links from partners
- `ReviewSite` - Partner review site configuration

### Console Commands

Artisan commands in `app/Console/Commands/` organized by feature:
- `DataSource/` - External data imports
- `Game/` - Game data processing
- `Review/` - Review imports and processing
- `Sitemap/` - Sitemap generation

## Key Patterns

### Dependency Injection

Use constructor injection over `new` instantiation:
```php
public function __construct(private SomeRepository $repo) {}
```

### Staff Game Lists

All staff list pages use a unified `showList()` method with `listConfig()` array. See `docs/dev-notes.md` for adding new lists.

### Console ID Context

Many queries require a console ID (Switch 1 vs Switch 2). Ensure console context is passed through data flows where needed.

## Testing

- `tests/Unit/` - Fast unit tests
- `tests/Feature/` - Fast integration tests
- `tests/Page/` - Slow browser-based tests (uses Symfony Panther)

Test suites configured in `phpunit.xml`:
- `Fast` - Unit + Feature (default)
- `Page` - Browser tests
- `All` - Everything

## Frontend

- Bootstrap (migrating from 3 to 5)
- Laravel Mix for asset compilation
- jQuery for interactivity
