<?php

/**
 * Kumki — a mahout starter plugin.
 *
 * The header below is what WordPress reads: the plugin name, the text domain that
 * matches this file's slug, and the minimum core and PHP the family supports.
 *
 * Plugin Name: Kumki
 * Description: A mahout starter plugin: the durable half of a site — content types, fields, storage and admin screens — installed as the site's root of record.
 * Version: 1.0.0
 * Requires at least: 7.1
 * Requires PHP: 8.4
 * Author: Iniznet
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kumki
 * Domain Path: /languages
 */

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

// Core resolves an activation callback by plugin basename, so the path that names
// this file has to reach the composition root; it is passed in rather than
// rediscovered, because a host that guessed its own basename would register the
// hook for a different installation than the one it shipped in.
Iniznet\Kumki\Bootstrap::run(__FILE__);
