<?php

/**
 * The declared field panels, loaded once from `config/fields.php`.
 *
 * Every admin surface the field layer needs — the metaboxes, the save entry, the
 * REST read bindings, the write-failure notice — is derived from this collection and
 * from nothing else, so a panel that is not declared there does not exist anywhere.
 *
 * The collection belongs to this plugin and the panel belongs to the package: the
 * host owns the declaration site, and the pairing every admin surface is built from
 * is `mahout-fields`' `FieldPanel`. This class answers to the package's `Panels`
 * contract, which is the only key the composition root binds it under.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Features\Fields;

use Iniznet\Mahout\Fields\Contracts\Panels;
use Iniznet\Mahout\Fields\FieldPanel;

final readonly class DeclaredPanels implements Panels
{
    /** @param list<FieldPanel> $panels */
    public function __construct(private array $panels)
    {
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return [] === $this->panels;
    }

    /**
     * @return list<FieldPanel>
     */
    #[\Override]
    public function forPostType(string $postType): array
    {
        return \array_values(\array_filter(
            $this->panels,
            static fn (FieldPanel $panel): bool => $panel->postType === $postType,
        ));
    }

    /**
     * @return \ArrayIterator<int, FieldPanel>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->panels);
    }
}
