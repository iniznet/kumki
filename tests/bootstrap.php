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
        // suite is a first install. Which means the ledger has to be empty first - it
        // is the one migration artefact that outlives the session, since the tables the
        // suite creates are temporary and the row recording them is not. Leave it and
        // the second run of this file believes its own schema is already in place and
        // creates nothing.
        // A test suite is a first install, so the artefacts the migrations create are
        // removed with the ledger that records them. The two belong together: leave the
        // ledger and the next run believes its schema is in place and creates nothing,
        // leave the tables and the next run meets CREATE TABLE with something already
        // there. Neither failure is about the code under test, and both read as SQL.
        $connection = Iniznet\Kumki\Bootstrap::services()->get(Iniznet\Mahout\Db\Contracts\SqlConnection::class);
        $prefix = $connection->prefix();
        $collate = $connection->charsetCollate();
        $identity = Iniznet\Mahout\Kernel\RuntimeIdentity::fromClass(Iniznet\Kumki\Bootstrap::class);

        // Both ledger names: the unsuffixed one predates identities, and
        // LegacyNameAdoption moves it onto the declared one inside the run below.
        // Clearing only the current name would let that adoption carry a stale
        // history into a suite that expects an empty ledger.
        $GLOBALS['wpdb']->query(
            'DROP TABLE IF EXISTS '.Iniznet\Mahout\Db\Identifier::prefixed($prefix, 'mahout_migrations')->quoted(),
        );

        foreach ([
            Iniznet\Mahout\Db\MigrationLedgerSchema::table($prefix, $identity, $collate),
            Iniznet\Mahout\Fields\FieldValuesTable::table($prefix, $collate),
            Iniznet\Mahout\Fields\FieldLeavesTable::table($prefix, $collate),
        ] as $table) {
            $GLOBALS['wpdb']->query('DROP TABLE IF EXISTS '.$table->name->quoted());
        }

        foreach (['db_schema_version', 'db_search_index', 'db_sweep_cursors'] as $suffix) {
            \delete_option($identity->namespacedName($suffix));
            \delete_option('mahout_'.$suffix);
        }

        Iniznet\Kumki\Bootstrap::services()
            ->get(Iniznet\Mahout\Db\MigrationRunner::class)
            ->migrate();

        // Then check the claim. A migration runner reads a ledger row as proof that a
        // schema artefact exists, and a ledger that outlives the tables it describes -
        // which one suite can do to another sharing one test database - makes it
        // conclude that everything is already in place and create nothing. The failure
        // then surfaces three queries later as "table doesn't exist", in a test that
        // never mentioned a migration. This is where that fact belongs.
        $prefix = $connection->prefix();
        $collate = $connection->charsetCollate();
        $identity = Iniznet\Mahout\Kernel\RuntimeIdentity::fromClass(Iniznet\Kumki\Bootstrap::class);
        $missing = [];

        foreach ([
            // Both ledger names: the unsuffixed one predates identities, and
            // LegacyNameAdoption moves it onto the declared one inside the run
            // below. Clearing only the current name would let that adoption carry a
            // stale history into a suite that expects an empty ledger.
            $prefix.'mahout_migrations',
            Iniznet\Mahout\Fields\FieldValuesTable::table($prefix, $collate),
            Iniznet\Mahout\Fields\FieldLeavesTable::table($prefix, $collate),
        ] as $table) {
            $name = $table->name->value;

            if (null === $GLOBALS['wpdb']->get_var('SHOW TABLES LIKE \''.$name.'\'')) {
                $missing[] = $name;
            }
        }

        if ([] !== $missing) {
            fwrite(STDERR, sprintf(
                'The migration reported success and created none of: %s.%sThe migrations ledger (%s) claims they exist. Delete that table - it is a claim about artefacts that are not there - or drop the test database, and run again.%s',
                implode(', ', $missing),
                PHP_EOL,
                Iniznet\Mahout\Db\MigrationLedgerSchema::nameFor($prefix, Iniznet\Mahout\Kernel\RuntimeIdentity::fromClass(Iniznet\Kumki\Bootstrap::class))->value,
                PHP_EOL,
            ));

            exit(1);
        }
    }
);

// Core's installer prints its progress, and the suite is quieter for discarding it:
// the boot above now says plainly what it could not do, which is the only thing a
// developer reading this file needs to see.
\ob_start();
require_once $testsDir.'/includes/bootstrap.php';
\ob_end_clean();
