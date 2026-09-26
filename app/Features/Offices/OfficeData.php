<?php

/**
 * One office, mapped: the facts a listing renders.
 *
 * `seats` is a field-layer value read from the value table and it arrives as the type
 * the field declares, never as a string that each consumer decodes for itself.
 * `contacts` is the repeater rebuilt from its leaves. Nothing here resolves a
 * collaborator, and nothing here is escaped — escaping is the render, and this object
 * has never heard of one.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Features\Offices;

final readonly class OfficeData
{
    /**
     * @param list<OfficeContact> $contacts
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?string $summary,
        public ?int $seats,
        public array $contacts = [],
    ) {
    }
}
