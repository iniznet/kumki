<?php

/**
 * The office repository: the feature's only reader, and the only place a request's
 * intent becomes a query.
 *
 * Three methods, and each one exists to show a different rule.
 *
 * `page()` is the ordinary listing. It asks {@see PostReader} for a page — the hardened
 * shape, the peek instead of a count, the priming — and then files every field the page
 * owns with one `FieldReader::prime()` call before mapping. That call is the whole
 * reason the storage target stays a storage decision: without it, twenty offices times
 * three fields would be sixty primary-key reads, and the page would cost more because
 * somebody wrote `Table` in a config file. Filed before mapping, exactly as the meta and
 * term caches are, it is one statement per store either way.
 *
 * `withSeatsAtLeast()` is why `office_seats` is Table at all: the field package's query
 * builder answers from the `(field_id, value_num)` index, and its object ids feed
 * `WP_Query` through `post__in` with `orderby` `post__in`, so the second query is the
 * one that assembles the page and the first is the one that knows which rows qualify.
 * An empty id list short-circuits before the second query is ever built.
 *
 * `countWithSeats()` is the aggregate, and it is capped by construction:
 * `countUpTo()` counts over a bounded inner read, so the answer is exact up to the
 * ceiling and equal to the ceiling above it. A caller that needs an exact total across
 * a large range needs a maintained counter, not a wider scan — the scan is the tax.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Features\Offices;

use Iniznet\Mahout\Content\PostPage;
use Iniznet\Mahout\Content\PostReader;
use Iniznet\Mahout\Content\QuerySpec;
use Iniznet\Mahout\Fields\Contracts\FieldQuery;
use Iniznet\Mahout\Fields\Contracts\FieldReader;
use Iniznet\Mahout\Fields\ObjectRef;
use Iniznet\Mahout\Fields\Operator;

final readonly class OfficesRepository
{
    /** The post type the feature declares in config/content-types.php. */
    public const string POST_TYPE = 'kumki_office';

    /** How many offices a filtered read may name, and how many rows one aggregate may count. */
    public const int LIMIT = 25;

    public function __construct(
        private PostReader $reader,
        private FieldReader $fields,
        private FieldQuery $query,
        private OfficeMapper $mapper,
    ) {
    }

    public function page(int $page, int $perPage): OfficesPage
    {
        return $this->collect($this->reader->fetch(new QuerySpec(
            filters: [
                'post_type' => self::POST_TYPE,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
            ],
            perPage: $perPage,
            offset: ($page - 1) * $perPage,
        )));
    }

    /**
     * The offices with at least `$seats` seats, in the order the index returned them.
     */
    public function withSeatsAtLeast(int $seats): OfficesPage
    {
        $ids = $this->query->postIds('office_seats', Operator::GreaterThanOrEqual, $seats, self::LIMIT);

        if ([] === $ids) {
            return new OfficesPage([], false);
        }

        return $this->collect($this->reader->fetch(new QuerySpec(
            filters: [
                'post_type' => self::POST_TYPE,
                'post_status' => 'publish',
                'post__in' => $ids,
                'orderby' => 'post__in',
            ],
            perPage: \count($ids),
        )));
    }

    /**
     * How many offices seat at least `$seats`, exact below the ceiling and equal to it
     * above: the ceiling is this feature's decision, not the storage layer's accident.
     */
    public function countWithSeats(int $seats, int $ceiling = self::LIMIT): int
    {
        return $this->query->countUpTo('office_seats', Operator::GreaterThanOrEqual, $seats, $ceiling);
    }

    /**
     * The one mapping path both reads share, so no listing can forget the prime.
     */
    private function collect(PostPage $rows): OfficesPage
    {
        $refs = \array_map(
            static fn (\WP_Post $post): ObjectRef => ObjectRef::post((int) $post->ID),
            $rows->posts,
        );

        $this->fields->prime($refs);

        $offices = [];

        foreach ($rows->posts as $post) {
            $ref = ObjectRef::post((int) $post->ID);
            $seats = $this->fields->value('office_seats', $ref);

            $offices[] = $this->mapper->office(
                $post,
                null === ($summary = $this->fields->value('office_summary', $ref)) ? null : (string) $summary,
                null === $seats ? null : (int) $seats,
                $this->contacts($ref),
            );
        }

        return new OfficesPage($offices, $rows->hasMore);
    }

    /**
     * The repeater rebuilt from its leaves. The field layer returns one entry per item
     * with its members keyed by id; anything else is a shape this feature does not own
     * and is skipped rather than coerced into a contact that was never entered.
     *
     * @return list<OfficeContact>
     */
    private function contacts(ObjectRef $ref): array
    {
        $items = $this->fields->items('office_contacts', $ref);
        $contacts = [];

        foreach ($items as $item) {
            if (!\is_array($item)) {
                continue;
            }

            $role = $item['contact_role'] ?? null;
            $name = $item['contact_name'] ?? null;

            if (!\is_string($role) || !\is_string($name)) {
                continue;
            }

            $contacts[] = new OfficeContact($role, $name);
        }

        return $contacts;
    }
}
