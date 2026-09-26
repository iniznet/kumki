<?php

/**
 * One page of offices.
 *
 * `hasMore` is the reader's peek, never a count: the query asked for one row more than
 * the page holds and that row is the answer, which is why no pagination count query is
 * ever issued. The plugin's own aggregate (`OfficesRepository::countWithSeats()`) is a
 * different thing with a different bound, and the capped form of it is what keeps that
 * one honest under load.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Features\Offices;

final readonly class OfficesPage
{
    /**
     * @param list<OfficeData> $offices
     */
    public function __construct(
        public array $offices,
        public bool $hasMore,
    ) {
    }
}
