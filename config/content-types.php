<?php

/**
 * The one declarative content model. Every entry is a mahout-content declaration: a
 * PostType, a Taxonomy or a RestRoute, never a raw register_post_type() call.
 *
 * This is the file the theme starter does not have, in the sense that matters: a
 * post type declared here survives a theme switch, because the plugin that declares
 * it is not the thing being switched. A content model a site is expected to keep
 * belongs to this host, and the boundary the corpus draws (BND-06) is decided by
 * which of the two files a declaration is written in.
 *
 * @return list<\Iniznet\Mahout\Content\PostType|\Iniznet\Mahout\Content\Taxonomy|\Iniznet\Mahout\Content\RestRoute>
 */

declare(strict_types=1);

return [];
