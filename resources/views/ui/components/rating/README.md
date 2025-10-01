### Rating cheatsheet

## Usage

```
{% import 'ui/components/rating/badge.twig' as rating %}

{{ rating.score(8.5) }}                  {# normal badge, B5, h4 #}
{{ rating.circle(6.2, 'h5', 'b3') }}     {# circle badge, smaller, legacy B3 #}
{{ rating.delisted() }}                  {# de-listed badge #}
{{ rating.tbc(2) }}                      {# 2/3 reviews #}
{{ rating.tbc(null, 'h4', 'b5', true) }} {# just TBC, no review text #}
```
