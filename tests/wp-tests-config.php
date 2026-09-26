<?php

declare(strict_types=1);

/**
 * WordPress test configuration for the generated plugin.
 *
 * This is a template. It points WordPress at the installed core (ABSPATH) but at a
 * database that is NOT the development site's. Core's test library drops every table
 * in DB_NAME, so keep the name on the _tests suffix.
 *
 * Copy to tests/wp-tests-config.php and edit the database values for your machine,
 * or point WP_TESTS_CONFIG_FILE_PATH at any other file.
 */
define('ABSPATH', 'F:/laragon/www/modernwp/');

/*
 * An installed theme, because a plugin suite has no theme to activate and core's
 * bootstrap insists on one.
 */
define('WP_DEFAULT_THEME', 'twentytwentyfour');

define('WP_DEBUG', true);

/* Separate database. The _tests suffix is the contract; do not edit it away. */
define('DB_NAME', 'modernwp_tests');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('AUTH_KEY', 'plugin-test-put-your-unique-phrase-here');
define('SECURE_AUTH_KEY', 'plugin-test-put-your-unique-phrase-here');
define('LOGGED_IN_KEY', 'plugin-test-put-your-unique-phrase-here');
define('NONCE_KEY', 'plugin-test-put-your-unique-phrase-here');
define('AUTH_SALT', 'plugin-test-put-your-unique-phrase-here');
define('SECURE_AUTH_SALT', 'plugin-test-put-your-unique-phrase-here');
define('LOGGED_IN_SALT', 'plugin-test-put-your-unique-phrase-here');
define('NONCE_SALT', 'plugin-test-put-your-unique-phrase-here');

$table_prefix = 'wptests_';

define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Test');

define('WP_PHP_BINARY', 'php');
define('WPLANG', '');
