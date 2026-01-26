# Switch Scores

A Laravel application for tracking Nintendo Switch game reviews and ratings. Aggregates review scores from multiple sources, maintains game databases, and provides public, staff, and member interfaces.

## Features

- **Review aggregation** - Collects and averages scores from partner review sites
- **Game database** - Comprehensive catalogue of Nintendo Switch games (Switch 1 & 2)
- **Categorisation** - Games organised by category, series, collection, and tags
- **Data sources** - Integration with Nintendo eShop APIs for pricing and release data
- **Quality filtering** - Identifies and filters low-quality releases
- **Staff tools** - Admin interface for managing games, reviews, and data imports

## Tech Stack

- **Framework:** Laravel 10
- **Templates:** Twig (via TwigBridge)
- **Frontend:** Bootstrap 5 (staff), Bootstrap 3 (public - migration in progress)
- **Database:** MySQL
- **Assets:** Laravel Mix

## Documentation

- [Local Development Setup](docs/local-setup.md)
- [Refactor Guide](docs/refactor-guide.md)
- [Changelog](docs/changelog.md)
- [Template Helpers](https://github.com/benbarden/switchscores/wiki/Template-helpers) (Wiki)

## Quick Start

```bash
# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run dev

# Start development server
php artisan serve
```

## Testing

```bash
# Run fast tests (Unit + Feature)
make test

# Run browser-based page tests
make test-page

# Run all tests
make test-all

# Run specific test
make test-filter F=testMethodName
```

## Project Structure

```
app/
├── Console/Commands/    # Artisan commands
├── Domain/              # Business logic (DDD structure)
├── Http/Controllers/    # Route handlers
└── Models/              # Eloquent models

resources/views/
├── theme/               # Layout templates
├── ui/components/       # Reusable UI components
├── staff/               # Staff admin pages
├── members/             # Member area pages
└── public/              # Public site pages

routes/
├── public.php           # Public routes
├── staff/               # Staff routes
├── members/             # Member routes
└── api.php              # API routes
```

## License

Proprietary - All rights reserved.
