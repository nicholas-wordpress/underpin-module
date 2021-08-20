# Nicholas Underpin Module

This module sets up the back-end logic necessary to use, and extend a nearly headless WordPress website.

## Boilerplate

This module is used in [a theme boilerplate](https://github.com/nicholas-wordpress/nearly-headless-theme). You probably want to use that directly.

## Installation

`composer require nicholas-wordpress/core`

## Extending

This is an [Underpin](github.com/underpin-WP/underpin) module, and the entrypoint for it is using the function `nicholas`, and it comes built-in with a handful of loaders:

1. Script loader
1. Rest Endpoint Loader
1. Meta Loader
1. Option Loader
1. Template Loader

## Scripts

This module assumes that your theme has 3 scripts built-into the `build` directory of your theme:

1. admin.js - Gets enqueued in the settings screen located in **Settings>>>Nicholas Settings**
1. editor.js - Gets enqueued on block editor pages
1. theme.js - Gets enqueued on front-end pages that are not using compatibility mode

## REST Endpoints

This module loads in a set of endpoints in the `nicholas/v1` namespace. These endpoints are used by the various scripts mentioned above to run the nearly-headless paradigm.