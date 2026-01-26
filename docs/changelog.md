# Changelog

Development history and completed work.

---

## 2026-01-26 — Staff Section Bootstrap 5 Migration

**Summary:**
Migrated all staff section templates from Bootstrap 3 to Bootstrap 5.

**Sections migrated:**
- News (8 files)
- Stats (3 files)
- Partners (8 files)
- Games Companies (9 files)
- Reviews (23 files)
- Games (40+ files)

**Key changes:**
- Layout extends changed from `theme/wos/staff/` to `theme/staff-b5/`
- Table sorting includes changed from `table-sorting-b3.twig` to `table-sorting-b5.twig`
- Form classes updated (`form-group` → `row mb-3`, `control-label` → `col-form-label`, etc.)
- Labels updated (`label label-*` → `badge bg-*`)
- Buttons updated (`btn-xs` → `btn-sm`, `btn-default` → `btn-outline-secondary`)
- Select elements updated (`form-control` → `form-select`)

**Additional improvements:**
- Added visual section styling to game editor form (fieldset backgrounds/borders)
- Fixed Data checks component (`ui/components/checks/row.twig`)
- Added `renderB5Horizontal` macro to category dropdown component

**Files updated outside staff templates:**
- `ui/components/checks/row.twig`
- `ui/components/taxonomy/category-dropdown.twig`
- `ui/components/staff/game/bulk-edit-table.twig`

---

## 2025-08-11 — Staff Game Lists Refactor

**Summary:**  
All staff game list pages are now handled through a single `showList()` method, using a unified `listConfig()` array and optional `getDynamicTitle()` helper.  
Previously, each list type had its own route, controller method, and (often) duplicate logic.

**Benefits:**
- One route (`staff.games.list.showList`) handles all lists.
- Titles and breadcrumbs for special cases (e.g. category, series, tag, format-option) are generated dynamically in `getDynamicTitle()`.
- Adding a new list requires only:
    1. Adding a config entry to `listConfig()` (with `title`, `fetch` closure, and optional `dynamicTitle` flag).
    2. Adding a matching case in `getDynamicTitle()` (if dynamic).
    3. Adding a link in the UI with `listType` and optional `param1`, `param2`.
- Future-proof for Laravel route model binding if adopted later.
- Templates now consistently use:
```twig
  {{ route('staff.games.list.showList', { 'listType': 'by-category', 'param1': category }) }}
```

**Example — Adding a New List:**

1. Add to `listConfig()`:
   ```php
   'new-list' => [
       'title' => 'My New List',
       'fetch' => function () {
           return $this->repoGameLists->newListMethod();
       },
   ],
   ```

2. (Optional) Add to `getDynamicTitle()` if it needs a dynamic title:
   ```php
   case 'new-list':
       return 'My New List';
   ```

3. Link to it from the UI:
   ```twig
   {{ route('staff.games.list.showList', { 'listType': 'new-list' }) }}
   ```

---

**Completed in this refactor:**
- 21 list types converted.
- Obsolete routes and controller methods removed.
- Verified all staff list pages work via `showList()`.
