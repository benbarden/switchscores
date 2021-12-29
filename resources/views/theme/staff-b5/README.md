# Staff theme

This folder contains theme files for Staff pages that have been migrated to Bootstrap v5.

This file will document some of the changes relevant to switchscores.

# Including the new theme

A new Bootstrap v5 theme file should start with the following:

`{% extends 'theme/staff-b5/layout-default.twig' %}`

# DataTables

To solve some styling issues, the Bootstrap 5 theme files should use a
newer version of DataTables. To include it, replace this:

`{% include 'common/table-sorting.twig' %}`

with this:

`{% include 'common/table-sorting-b5.twig' %}`

# Labels

These are now badges. See https://getbootstrap.com/docs/5.0/components/badge/

They can mostly be swapped as follows:

`label label-primary` becomes `badge bg-primary`

A new version of `modules/rating/badge.twig` can be found under `modules/rating/badge-b5.twig`.

# Forms

Forms have changed a lot and will need quite a bit of work to get them 
looking good. A good example is in `staff/invite-code/form.twig`.

# Text alignment

Replace instances of `class="text-right` with `class="text-end"`.

# Tables

Replace `table-condensed` with `table-sm`.
