<?php

/**
 * The worked example, executed.
 *
 * This file is the part a developer reads after the two config files, because it is
 * where the claims the starter makes are shown to hold: that a field's storage target
 * decides nothing about the call site, that the page-prime replaces the per-field
 * multiplication rather than rearranging it, that the filtered read is a two-phase
 * query whose first phase is an index lookup, and that the aggregate saturates at its
 * ceiling instead of scanning the range.
 *
 * Every statement count here is read from wpdb's own buffer with SAVEQUERIES on, which
 * is what the theme's Surface ceiling tests use too. A test that asserted the prime
 * happened by checking a return value would pass even if it issued twelve queries.
 *
 * @internal
 */
declare(strict_types=1);

namespace Iniznet\Kumki\Tests\Integration;

use Iniznet\Kumki\Features\Offices\OfficeData;
use Iniznet\Kumki\Features\Offices\OfficesRepository;
use Iniznet\Mahout\Fields\ObjectRef;
use Iniznet\Mahout\Fields\StorageTarget;
use Iniznet\Mahout\Kernel\Container;

final class OfficesTest extends \WP_UnitTestCase
{
    private Container $services;

    private OfficesRepository $offices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->services = \Iniznet\Kumki\Bootstrap::services();

        // The two field tables are created by a migration, which puts them outside the
        // transaction core wraps every case in: rows from one case are still there for
        // the next. The package's own helper empties them, because the alternative - a
        // number no other case uses, repeated in each - passes for the wrong reason the
        // day somebody adds a sixth case to this file.
        (new \Iniznet\Mahout\Fields\TestSupport\FieldTables(
            $this->services->get(\Iniznet\Mahout\Db\Contracts\SqlConnection::class),
        ))->reset();

        $this->offices = $this->services->get(OfficesRepository::class);
    }

    public function testTheDeclaredFieldGroupsAndTheirTargetsAreTheOnesTheConfigsSay(): void
    {
        /** @var \Iniznet\Mahout\Fields\Contracts\FieldRegistry $registry */
        $registry = $this->services->get(\Iniznet\Mahout\Fields\Contracts\FieldRegistry::class);

        // The whole point of requiring an explicit target: reading it back is a
        // question with one answer, and a wrong one is a loud failure rather than a
        // field that silently stops being filterable.
        self::assertSame(StorageTarget::Meta, $registry->resolve('office_summary')->storage);
        self::assertSame(StorageTarget::Table, $registry->resolve('office_seats')->storage);
        self::assertSame(StorageTarget::Table, $registry->resolve('office_contacts')->storage);
    }

    public function testAQueriedRepeaterIsDeclaredAsOne(): void
    {
        /** @var \Iniznet\Mahout\Fields\Contracts\FieldRegistry $registry */
        $registry = $this->services->get(\Iniznet\Mahout\Fields\Contracts\FieldRegistry::class);
        $field = $registry->resolve('office_contacts')->field;

        // `queried` is the developer's declaration that the repeater will be searched:
        // its leaves are indexed per member and addressed member-qualified, and the
        // cost is the price of that decision rather than the theme's accident. A member
        // declares Carried because the root's target stores every leaf.
        self::assertInstanceOf(\Iniznet\Mahout\Fields\RepeaterField::class, $field);
        self::assertTrue($field->queried, 'office_contacts is searchable by member.');
        self::assertSame(5, $field->expectedMaxItems, 'the ceiling an over-long submission is checked against.');

        // Members are not registered ids of their own: the registry keys the root and
        // the leaves table stores each member against its address, which is why the
        // searchable form is the member-qualified pair (office_contacts.contact_role)
        // and why a member declares Carried.
        self::assertInstanceOf(\Iniznet\Mahout\Fields\RepeaterItem::class, $field->item);
        self::assertCount(2, $field->item->fields);
    }

    public function testAnOfficeRoundTripsThroughBothTargetsAndComesBackTyped(): void
    {
        $id = $this->office('Chefoo', 'Two rooms by the harbour', 1222);
        $ref = ObjectRef::post($id);

        $fields = $this->services->get(\Iniznet\Mahout\Fields\Contracts\FieldReader::class);

        self::assertSame('Two rooms by the harbour', $fields->value('office_summary', $ref), 'Meta read');
        self::assertSame(1222, $fields->value('office_seats', $ref), 'Table read, arriving as the int it declares');

        $page = $this->offices->page(1, 10);

        self::assertCount(1, $page->offices);
        self::assertInstanceOf(OfficeData::class, $page->offices[0]);
        self::assertSame($id, $page->offices[0]->id);
        self::assertSame(1222, $page->offices[0]->seats);
    }

    public function testAPageOfOfficesFilesItsFieldRowsOnceAndNotOncePerField(): void
    {
        $ids = [];

        foreach ([['A', 4], ['B', 8], ['C', 16], ['D', 2], ['E', 30]] as [$title, $seats]) {
            $ids[] = $this->office($title, 'summary', $seats);
        }

        // The prime is the difference between one statement for the page and one per
        // object per field. With three scalar fields in play, an unprimed read of five
        // offices would be a multiple of five; the assertion below is that it is one.
        $before = $this->statements();
        $page = $this->offices->page(1, 10);
        $used = $this->statements() - $before;

        // The page may hold offices another case left behind — the field tables are
        // custom tables and do not ride core's test transaction — so the assertion is
        // about *these* rows and about how many statements the page cost, not about a
        // pristine table.
        $seen = \array_map(static fn (OfficeData $office): int => $office->id, $page->offices);

        foreach ($ids as $id) {
            self::assertContains($id, $seen, 'every office this case created is on the page.');
        }

        self::assertSame(1, $this->primeStatements($before), 'the value table is read once for the whole page.');
        self::assertSame(\count($page->offices), \count($seen), 'no row is mapped twice.');

        // And the boundary moved where a declaration made it movable. This group
        // declares expectedMaxItems, the writer enforces it, and so the ceiling is
        // derivable and one statement brings home every contact on the page. A
        // repeater that declares no bound is still one read per object per group
        // (mahout-fields ADR-0011): the sentence worth reading before adding a
        // queried repeater to a card is not "repeaters cost per object" but "declare
        // a bound or pay per object", and the number below is which one this card is.
        self::assertSame(
            1,
            $this->leafStatements($before),
            'one leaves read for the whole page, because office_contacts declares its bound.',
        );
        self::assertLessThanOrEqual(
            2 + \count($page->offices),
            $used,
            sprintf('the page cost %d statements: %s', $used, \implode(' | ', $this->lastQueries($before))),
        );
    }

    public function testTheFilteredReadIsAnIndexLookupFollowedByOnePageQuery(): void
    {
        // Seat values unique to this case, because the value table keeps rows across
        // cases: the filtered read must be provable rather than merely plausible.
        $this->office('Tiny', 'a', 9001);
        $this->office('Medium', 'b', 9040);
        $this->office('Large', 'c', 9400);

        $page = $this->offices->withSeatsAtLeast(9040);

        self::assertSame(['Medium', 'Large'], \array_map(static fn (OfficeData $o): string => $o->title, $page->offices), 'the index answer drives the page order.');

        $before = $this->statements();
        $this->offices->withSeatsAtLeast(999999);
        $queries = $this->queriesSince($before);

        self::assertCount(1, $queries, 'an empty first phase short-circuits: no second query is built for no ids.');
        self::assertStringContainsString('mahout_field_values', $queries[0] ?? '');
    }

    public function testTheAggregateSaturatesAtItsCeilingInsteadOfScanning(): void
    {
        // The tables are emptied for every case, so this count is this case's rows and
        // nothing else's: the aggregate's bound is the assertion.
        foreach (range(0, 4) as $row) {
            $this->office('Saturated '.$row, 'summary', 777000 + $row);
        }

        self::assertSame(5, $this->offices->countWithSeats(777000, 10), 'exact below the ceiling.');
        self::assertSame(3, $this->offices->countWithSeats(777000, 3), 'and equal to the ceiling above it.');

        $before = $this->statements();
        $this->offices->countWithSeats(777000, 2);

        self::assertCount(1, $this->queriesSince($before));
        self::assertStringContainsString('FROM (SELECT 1 FROM', $this->queriesSince($before)[0] ?? '', 'the count runs over the capped inner read.');
    }

    public function testTheRepeaterComesBackAsItemsAndNotAsAnEnvelope(): void
    {
        $id = $this->office('HQ', 'summary', 8123);
        $ref = ObjectRef::post($id);

        $this->services->get(\Iniznet\Mahout\Fields\Contracts\FieldWriter::class)->setItems(
            'office_contacts',
            $ref,
            [
                ['contact_role' => 'management', 'contact_name' => 'Ada'],
                ['contact_role' => 'facilities', 'contact_name' => 'Bo'],
            ],
        );

        $contacts = [];

        foreach ($this->offices->page(1, 10)->offices as $office) {
            if ($office->id === $id) {
                $contacts = $office->contacts;
            }
        }

        self::assertCount(2, $contacts);
        self::assertSame('management', $contacts[0]->role);
        self::assertSame('Ada', $contacts[0]->name);
        self::assertSame('facilities', $contacts[1]->role);
    }

    public function testThePostTypeAndTaxonomyThePluginDeclaresAreRegistered(): void
    {
        flush_rewrite_rules();

        self::assertContains('kumki_office', \array_keys(\get_post_types()), 'the durable content model is declared by this host');
        self::assertContains('kumki_office_region', \get_object_taxonomies('kumki_office'));
    }

    private function office(string $title, string $summary, int $seats): int
    {
        $id = (int) self::factory()->post->create([
            'post_type' => OfficesRepository::POST_TYPE,
            'post_title' => $title,
            'post_status' => 'publish',
        ]);

        $writer = $this->services->get(\Iniznet\Mahout\Fields\Contracts\FieldWriter::class);
        $ref = ObjectRef::post($id);

        $writer->set('office_summary', $ref, $summary);
        $writer->set('office_seats', $ref, $seats);

        return $id;
    }

    private function statements(): int
    {
        global $wpdb;

        return \count((array) ($wpdb->queries ?? []));
    }

    /**
     * @return list<string>
     */
    private function queriesSince(int $mark): array
    {
        global $wpdb;

        return \array_map(
            static fn (mixed $record): string => (string) ((array) $record)[0],
            \array_slice((array) ($wpdb->queries ?? []), $mark),
        );
    }

    /**
     * How many of the statements since $mark hit the value table: the prime's own
     * count, separated from the rows the reader fetched.
     */
    private function primeStatements(int $mark): int
    {
        $hits = 0;

        foreach ($this->queriesSince($mark) as $query) {
            if (1 === preg_match('/mahout_field_values/', $query)) {
                ++$hits;
            }
        }

        return $hits;
    }

    private function leafStatements(int $mark): int
    {
        $hits = 0;

        foreach ($this->queriesSince($mark) as $query) {
            if (1 === \preg_match('/mahout_field_leaves/', $query)) {
                ++$hits;
            }
        }

        return $hits;
    }

    /**
     * @return list<string>
     */
    private function lastQueries(int $mark, int $limit = 4): array
    {
        return \array_slice($this->queriesSince($mark), -$limit);
    }
}
