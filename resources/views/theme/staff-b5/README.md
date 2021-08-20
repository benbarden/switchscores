# Staff theme

This folder contains theme files for Staff pages that have been migrated to Bootstrap v5.

This file will document some of the changes relevant to switchscores.

# Including the new theme

A new Bootstrap v5 theme file should start with the following:

`{% extends 'theme/staff-b5/layout-default.twig' %}`

# Text alignment

Replace instances of `class="text-right` with `class="text-end"`.

# Tables

Replace `table-condensed` with `table-sm`.
