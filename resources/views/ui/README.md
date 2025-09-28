# UI: Components, Blocks, Layouts, Theme

This folder contains the **shared building blocks** for Switch Scores templates.  
Anything that’s reused across multiple parts of the site should live here, not inside `public/`, `staff/`, `user/`, etc.

---

## Folder structure

### `macros/`
Reusable Twig macros (functions).  
Keep these small and focused — e.g. date formatting, rendering a star rating, outputting a price.

Example usage: `{{ macros.rating.stars(game.rating) }}`

### `components/`
The smallest reusable UI pieces (atoms).  
These don’t usually know about context — they just render one thing.

Examples:
- `game-card.twig` → card with artwork, title, score
- `game-table.twig` → table row/columns for games
- `heading.twig` → styled heading
- `snapshot.twig` → stats snapshot block

### `blocks/`
Mid-level assemblies (molecules/sections).  
Blocks combine multiple components into a specific pattern used across the site.

Examples:
- `highlights.twig` → “Top Rated + Hidden Gems” section
- `publishers.twig` → “Who’s publishing?” list
- `low-quality.twig` → list of low quality releases

### `layouts/`
Page-level structures.  
Layouts are wrappers that define the overall structure and extend a theme template.

Examples:
- `standard.twig`
- `modern-debut.twig`

### `theme/`
Base templates and theme system (Bootstrap).  
This is where the root templates live that everything else extends from.

- **Bootstrap 3**: legacy templates
- **Bootstrap 5**: new templates
- Goal: migrate everything to Bootstrap 5 over time

### `includes/`
Tiny fragments or helpers that don’t fit elsewhere.  
Examples: row breaks, pagination fragments.

---

## Conventions
- New reusable code belongs here.
- Decide by scope:
    - **macro** = tiny helper function
    - **component** = smallest UI unit (card, table row, heading)
    - **block** = composed section (highlights, publishers)
    - **layout** = page-level template
- Keep **naming clear**: `game-card.twig` is better than `card2.twig`.

---

## Migration notes
- Over time, fold old `modules/` and `components/` into this structure.
- Bootstrap 3 and 5 should be split clearly in `theme/` until migration is complete.

---

## Visual hierarchy

### Filesystem (all at the same level under `ui/`)

```
ui/
├── macros/       # Tiny Twig helper functions
├── components/   # Smallest reusable UI parts (cards, tables, headings)
├── blocks/       # Mid-level assemblies made of components
├── layouts/      # Page-level wrappers
├── theme/        # Base templates (Bootstrap 3/5)
└── includes/     # Tiny fragments/helpers
```

---

### Conceptual usage flow

```
Page (e.g. month.twig)
    → Layout (page-level wrapper, e.g. `standard.twig`)
        → Block (section, e.g. `highlights.twig`)
            → Component (UI unit, e.g. `game-card.twig`)
                → Macro (helper function, e.g. `rating.stars`)
```
