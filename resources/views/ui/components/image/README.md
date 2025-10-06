# Image cheatsheet

## Header image

```
{% import 'ui/components/image/resolve.twig' as image %}
<img src="{{ image.header(game) }}" alt="{{ game.title }}">
```

## Square image

```
{% import 'ui/components/image/resolve.twig' as image %}
<img src="{{ image.square(game) }}" alt="{{ game.title }}">
```
