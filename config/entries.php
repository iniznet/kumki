<?php

/**
 * The one explicit Vite entry list. Every entry names its handle, its source, its
 * context and nothing else implicitly; there is no default and no inference, and
 * nothing scans the filesystem. vite.config.ts consumes this list; features append
 * to it, never around it.
 *
 * A declaration looks like:
 *
 *     [
 *         'handle'  => 'kumki-app',
 *         'source'  => 'resources/js/app.ts',
 *         'context' => 'front',
 *     ]
 *
 * 'context' is 'front' or 'admin': the admin entries are enqueued only on this
 * plugin's own screens, which is what keeps the field layer's stylesheet from
 * reaching the front end.
 *
 * @return list<array<string, mixed>>
 */

declare(strict_types=1);

return [];
