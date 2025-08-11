# Dev Notes

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
