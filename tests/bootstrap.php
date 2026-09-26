<?php

/**
 * The plugin's test bootstrap.
 *
 * Resolves core's first-party test library from WP_TESTS_DIR and the test
 * configuration from WP_TESTS_CONFIG_FILE_PATH. A missing variable falls back to the
 * harness locations on the workstation that shipped this scaffold and is overridable;
 * they are a publishing item, not a contract.
 *
 * The main plugin file is required from `muplugins_loaded`, exactly once for the
 * whole suite: that file is the production entry point, so the suite boots the graph
 * the site boots, and requiring it twice would register every provider twice — the
 * second call being the one that fails, by the composition root's own design.
 */

declare(strict_types=1);

// Every statement is observable through wpdb's own buffer, which is how the
// query-budget assertions read the exact statements a page ran.
if (!defined('SAVEQUERIES')) {
    define('SAVEQUERIES', true);
}

$testsDir = getenv('WP_TESTS_DIR');
if (false === $testsDir || '' === $testsDir) {
    $testsDir = 'F:/kerjaan 2/WordPress/libraries/wordpress-develop/tests/phpunit';
}

if (!file_exists($testsDir.'/includes/bootstrap.php')) {
    fwrite(STDERR, sprintf('WP_TESTS_DIR does not contain includes/bootstrap.php: %s%s', $testsDir, PHP_EOL));
    exit(1);
}

$configFile = getenv('WP_TESTS_CONFIG_FILE_PATH');
if (false === $configFile || '' === $configFile || !file_exists($configFile)) {
    $configFile = __DIR__.'/wp-tests-config.php';

    if (!file_exists($configFile)) {
        fwrite(STDERR, 'Copy tests/wp-tests-config.php.dist to tests/wp-tests-config.php, or set WP_TESTS_CONFIG_FILE_PATH.'.PHP_EOL);
        exit(1);
    }
}

define('WP_TESTS_CONFIG_FILE_PATH', $configFile);
define('WP_PHPUNIT__TESTS_CONFIG', $configFile);
define('WP_PHPUNIT__POLYFILLS_PATH', dirname(__DIR__).'/vendor/yoast/phpunit-polyfills');

require_once dirname(__DIR__).'/vendor/autoload.php';
require_once $testsDir.'/includes/functions.php';

tests_add_filter(
    'muplugins_loaded',
    static function (): void {
        // This plugin is the system under test. Requiring its main file runs the
        // composition root and registers the activation migration against the real
        // path, so the suite exercises the same entry production does.
        require_once dirname(__DIR__).'/kumki.php';

        // The tables come from the same migrations a live activation runs: a test
        // suite is a first install.
        Iniznet\Kumki\Bootstrap::services()
            ->get(Iniznet\Mahout\Db\MigrationRunner::class)
            ->migrate();
    }
);

// Core's installer is a subprocess that prints its progress. The buffer is
// discarded so nothing is sent before PHPUnit starts.
\ob_start();
require_once $testsDir.'/includes/bootstrap.php';
\ob_end_clean();
