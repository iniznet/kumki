<?php

/**
 * Uninstall refuses to guess.
 *
 * WordPress runs this file when the plugin is deleted from the admin, which is the
 * one moment a host can destroy data no theme switch would touch. A starter does not
 * ship that decision, because the correct answer depends on what the installation
 * declared — a content model with posts in it is deleted by nobody without a human
 * deciding, and a site-wide option is deleted by whoever owns it.
 *
 * So this file removes nothing, and the operator's paths are named here rather than
 * left to be discovered after a deletion:
 *
 * - content: the post types and taxonomies in `config/content-types.php` keep their
 *   rows; deleting content is a decision with a backup in front of it.
 * - field rows: `wp_mahout_field_values` and `wp_mahout_field_items` belong to the
 *   field layer, and the orphan sweep in mahout-db is what removes rows whose object
 *   is gone — a sweep, not an uninstall.
 * - schema: `wp vendor/bin/mahout-devtools` and `wp mahout migrate` are the owned
 *   paths for the ledger and the migrations, and a reversal runs `down()`.
 * - options: a host that declares option-screen values deletes its own keys here when
 *   it declares them, in the same change, and nowhere else.
 *
 * An uninstall that silently dropped the tables would be a data-loss path reachable
 * by one click in the admin, which is the opposite of what the storage layer exists
 * to prevent.
 */

declare(strict_types=1);

if (!\defined('WP_UNINSTALL_PLUGIN')) {
    \exit;
}
