# Taxonomy

## Category dropdown

```
{% import 'ui/components/taxonomy/category-dropdown.twig' as categorySelect %}

{{ categorySelect.render(
'category_id',
'Category',
CategoryList,
game.category_id ?? null,
true
) }}
```