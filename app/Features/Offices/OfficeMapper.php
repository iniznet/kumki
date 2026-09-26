<?php

/**
 * The only place a `WP_Post` becomes an office.
 *
 * The row arrives primed — post cache, meta cache, term cache, author cache — and the
 * field values arrive read from the field layer, so a mapper costs no statements of its
 * own. `get_post_meta()` appears nowhere in this file or anywhere in this plugin: the
 * field layer is the only read path for a registered field, and that is the rule which
 * makes the storage target invisible at the call site.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Features\Offices;

final readonly class OfficeMapper
{
    /**
     * @param list<OfficeContact> $contacts
     */
    public function office(\WP_Post $post, ?string $summary, ?int $seats, array $contacts): OfficeData
    {
        return new OfficeData(
            id: (int) $post->ID,
            title: (string) $post->post_title,
            summary: $summary,
            seats: $seats,
            contacts: $contacts,
        );
    }
}
