<?php

/**
 * One contact from the office's repeater.
 *
 * A repeater stores no envelope — each leaf is one scalar at its address — so the
 * mapper rebuilds the shape from the leaf rows rather than unserialising anything. The
 * role is the address `office_contacts.{index}.contact_role` and the name is its
 * sibling; that pairing is why the repeater is an item and not two parallel lists.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Features\Offices;

final readonly class OfficeContact
{
    public function __construct(
        public string $role,
        public string $name,
    ) {
    }
}
