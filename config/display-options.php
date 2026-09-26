<?php

/**
 * The site-wide settings this plugin declares — the option-screen half of the field
 * model.
 *
 * A setting that must survive a theme switch belongs here rather than in a
 * `theme_mod` or an `add_options_page()` call at file scope, because the settings
 * page, its save entry and its write-failure notice are all derived from the
 * declaration: an option screen that is not declared here does not exist anywhere.
 *
 * An option-context field is always `Meta`: the option is the singleton the values
 * hang off, and there is no object to index. The example declares none, and the
 * Office feature reads its two fields from the post context — which is the point of
 * keeping this file in the starter: it is where the third kind of field lands.
 *
 * Labels are plain strings, for the same reason as every other declaration.
 *
 * @return list<\Iniznet\Mahout\Fields\OptionScreen>
 */

declare(strict_types=1);

return [];
