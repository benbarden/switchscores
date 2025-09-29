# Form components

This folder contains reusable UI macros for forms.

## Submit button (`submit.twig`)

The submit button is used across forms, with variations for button type
and Bootstrap version.

### Variants
- `primary` (default)
- `danger` (for destructive actions, e.g. deletion)

### Bootstrap versions
- **B5** (default): uses `<div class="row mb-3">`
- **B3** (legacy): uses `<div class="form-group">`

### Macros
- `renderB5(text='Submit', variant='primary')`
- `renderB3(text='Submit', variant='primary')`

Example:
```twig
{% import 'ui/components/form/submit.twig' as formsubmit %}

{{ formsubmit.renderB5('Save changes') }}
{{ formsubmit.renderB3('Confirm deletion', 'danger') }}
```

### Cleanup note

Once Bootstrap 3 is fully removed, delete the renderB3 macro
and simplify submit.twig.
