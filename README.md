# worldofswitch

## Template helpers

### LinkHelper: gameShow

Use `LinkHelper.gameShow(game)` to generate a URL to a game detail page. Pass an instance of `App\Game` as shown.

The below example assuming you're looping through a collection of `App\Game` instances. E.g. `{{ for item in games }}`.

```
<a href="{{ LinkHelper.gameShow(item.game) }}">{{ item.game.title }}</a>
```

### DateHelper: isNew

Use `DateHelper.isNew(date)` to check if a date is within the last 7 days. Returns true/false.

```
{{ if DateHelper.isNew(item.release_date) }}
```

### SerialHelper: unserialize

Currently this has a very specific use case. It is used in Admin screens for quickly converting a serialised array 
into an array that Twig can loop through.

```
{% set ModFields = SerialHelper.unserialize(item.modified_fields) %}
{% if ModFields %}
<ul>
{% for field in ModFields %}
<li>{{ field }}</li>
{% endfor %}
</ul>
{% endif %}
```
