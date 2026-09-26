<?php

/**
 * The plugin's declared content model — the worked example, and the first thing to
 * read if you are asking "why a plugin rather than the theme?".
 *
 * These two entries are the whole difference. A post type declared here and its terms
 * survive switching the theme, because the plugin that declares them is not the thing
 * being switched; the same declaration in a theme means every row of it is orphaned
 * the day the site changes its look. That is the rule in the corpus (BND-06) and this
 * file is what obeying it looks like.
 *
 * Every entry is registered on init through the content package's Contracts surface:
 * the theme's and this plugin's ContentProvider both read a file of exactly this shape,
 * and the package's Registrar applies the naming rules, the reserved list and the
 * collision check before core sees anything. Nothing here calls `register_post_type()`,
 * and nothing is registered at file scope.
 *
 * Delete both entries and the feature stops existing: the panel renders nothing, the
 * repository finds no rows, and the schema keeps no rows of its own.
 *
 * Labels are plain strings: a declaration is read at boot, before the text domain
 * loads, so a declaration cannot translate.
 *
 * @return list<\Iniznet\Mahout\Content\PostType|\Iniznet\Mahout\Content\Taxonomy|\Iniznet\Mahout\Content\RestRoute>
 */

declare(strict_types=1);

use Iniznet\Mahout\Content\PostType;
use Iniznet\Mahout\Content\Taxonomy;

return [
    new PostType('kumki_office', [
        'labels' => [
            'name' => 'Offices',
            'singular_name' => 'Office',
            'all_items' => 'All offices',
            'add_new_item' => 'Add new office',
        ],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-building',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'rewrite' => ['slug' => 'offices'],
    ]),
    new Taxonomy('kumki_office_region', ['kumki_office'], [
        'labels' => [
            'name' => 'Regions',
            'singular_name' => 'Region',
        ],
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
    ]),
];
