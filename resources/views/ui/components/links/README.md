# Links cheatsheet

## Game: Edit

```
{% import 'ui/components/links/game.twig' as gamelink %}
{{ gamelink.edit(item) }}
```

## Game: Title

### Public
```
{% import 'ui/components/links/game.twig' as gamelink %}
{{ gamelink.title(item) }}
```

### Staff
```
{% import 'ui/components/links/game.twig' as gamelink %}
{{ gamelink.title(item, 'staff') }}
```
