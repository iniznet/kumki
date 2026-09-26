<?php

/**
 * The declared option screens, loaded once from `config/display-options.php`.
 *
 * A site-wide setting that must outlive the theme is declared here rather than
 * built as an `add_options_page()` call at file scope: the settings page, its save
 * entry and its write-failure notice are all derived from this collection, so a
 * screen that is not declared does not exist.
 *
 * The collection belongs to this plugin and the screen belongs to the package; the
 * class answers to the package's `OptionScreens` contract, which is the only key the
 * composition root binds it under.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Features\Fields;

use Iniznet\Mahout\Fields\Contracts\OptionScreens as OptionScreensContract;
use Iniznet\Mahout\Fields\OptionScreen;

final readonly class DeclaredOptionScreens implements OptionScreensContract
{
    /** @param list<OptionScreen> $screens */
    public function __construct(private array $screens)
    {
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return [] === $this->screens;
    }

    /**
     * @return \ArrayIterator<int, OptionScreen>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->screens);
    }
}
