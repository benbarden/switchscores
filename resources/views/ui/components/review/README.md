# Review cheatsheet

## Usage

### Quick reviews

With titles

```
{% import 'ui/components/review/quick-review.twig' as quickReview %}
{{ quickReview.render(QuickReview, true) }}
```

No titles

```
{% import 'ui/components/review/quick-review.twig' as quickReview %}
{{ quickReview.render(QuickReview) }}
```
