# Nicholas Underpin Module

This module sets up the back-end logic necessary to use, and extend a nearly headless WordPress website.

## Boilerplate

This module is used in [a theme boilerplate](https://github.com/nicholas-wordpress/nearly-headless-theme). You probably want to use that directly.

## Installation

`composer require nicholas-wordpress/core`

## Extending

This is an [Underpin](github.com/underpin-WP/underpin) module, and the entrypoint for it is using the function
`nicholas`, and it comes built-in with a handful of loaders:

1. Script loader
2. Rest Endpoint Loader
3. Meta Loader
4. Option Loader
5. Template Loader
6. Decision List Loader

## Scripts

This module assumes that your theme has 3 scripts built-into the `build` directory of your theme:

1. admin.js - Gets enqueued in the settings screen located in **Settings>>>Nicholas Settings**
2. editor.js - Gets enqueued on block editor pages
3. theme.js - Gets enqueued on front-end pages that are not using compatibility mode
4. sessionManager.js - Forces a session to clear the cache if the `nicholas_flush_cache` cookie is set.

## REST Endpoints

This module loads in a set of endpoints in the `nicholas/v1` namespace. These endpoints are used by the various scripts
mentioned above to run the nearly-headless paradigm.