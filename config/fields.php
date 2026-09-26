<?php

/**
 * The field-panel declarations — the second half of the worked example, and the file
 * that answers "when do I use Table instead of Meta?" with a real answer rather than a
 * sentence.
 *
 * The rule is one question: **will this be queried?** A field that is only ever read
 * along with its office is `Meta` — it rides the meta cache the reader already primes,
 * so any number of them costs the page nothing extra. A field that is filtered, sorted,
 * aggregated or counted is `Table`, because `wp_postmeta` has no `meta_value` index
 * worth the name and every clause against it is a join.
 *
 * `office_summary` is Meta: a line of copy read with the office, never filtered.
 * `office_seats` is Table: the repository's `withSeatsAtLeast()` filters on it and
 * `countWithSeats()` counts it, and both go through the `(field_id, value_num)` index
 * instead of a scan. `office_contacts` is a queried repeater, so its leaves live in the
 * leaves table and are addressed member-qualified — `office_contacts.contact_role` —
 * because a repeater that is searched is the developer's decision, and slower is that
 * decision's price rather than the theme's accident.
 *
 * A repeater stores no envelope: every leaf is one scalar at its address, and a member
 * declares `Carried` storage because the root's target stores every leaf. Nesting is
 * legal to three levels; this one is a single item, the common case.
 *
 * The field package's admin provider derives every metabox, save entry, field route,
 * write-failure notice and list column from this list. This file declares no admin code
 * because there is none to declare: delete the entry and the panel stops existing.
 *
 * Labels are plain strings, for the same reason as the content model's.
 *
 * @return list<\Iniznet\Mahout\Fields\FieldPanel>
 */

declare(strict_types=1);

use Iniznet\Mahout\Fields\ChoiceField;
use Iniznet\Mahout\Fields\FieldGroup;
use Iniznet\Mahout\Fields\FieldPanel;
use Iniznet\Mahout\Fields\IntegerField;
use Iniznet\Mahout\Fields\ObjectContext;
use Iniznet\Mahout\Fields\RepeaterField;
use Iniznet\Mahout\Fields\RepeaterItem;
use Iniznet\Mahout\Fields\StorageTarget;
use Iniznet\Mahout\Fields\TextField;

return [
    new FieldPanel('kumki_office', new FieldGroup('office_details', ObjectContext::Post, [
        new TextField('office_summary', StorageTarget::Meta, label: 'Summary'),
        new IntegerField('office_seats', StorageTarget::Table, label: 'Seats'),
        new RepeaterField(
            'office_contacts',
            StorageTarget::Table,
            new RepeaterItem([
                new ChoiceField('contact_role', StorageTarget::Carried, ['front-desk', 'management', 'facilities'], label: 'Role'),
                new TextField('contact_name', StorageTarget::Carried, label: 'Name'),
            ]),
            expectedMaxItems: 5,
            queried: true,
            label: 'Contacts',
        ),
    ], label: 'Office details')),
];
