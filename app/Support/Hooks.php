<?php

/**
 * Every hook this plugin emits or observes. Names are declared once, here.
 *
 * The domain prefix is the host's slug, the same rule the theme follows
 * (`kumki/{domain}/{event}`): a hook name says who owns the event, so a consumer
 * can tell the plugin's seam from the theme's without reading either.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Support;

final class Hooks
{
    /**
     * Core's init. ContentProvider registers the declared content model here, after
     * every provider booted, which is where a post type must be declared for the
     * rest of the request to see it.
     *
     * @since 1.0
     *
     * @action
     */
    public const string INIT = 'init';

    /**
     * Core's admin menu. AdminProvider attaches the pages this plugin owns here;
     * the field package attaches the panels and screens the declarations imply.
     *
     * @since 1.0
     *
     * @action
     */
    public const string ADMIN_MENU = 'admin_menu';

    /**
     * The cache purge seam. This plugin owns the seam and emits this action when a
     * content change invalidates a fragment; the client owns the endpoint that
     * listens. No vendor purge API is ever called.
     *
     * Declared in this scaffold; the invalidation service that fires it belongs to a
     * feature slice, and nothing fires it yet.
     *
     * @since 1.0
     *
     * @action
     *
     * @param list<string> $keys the fragment keys the purge covers
     */
    public const string PURGE = 'kumki/cache/purge';
}