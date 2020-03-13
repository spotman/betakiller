<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface UrlElementForMenuInterface extends UrlElementWithLabelInterface
{
    public const OPTION_MENU_NAME    = 'menu';
    public const OPTION_MENU_ORDER   = 'order';
    public const OPTION_MENU_COUNTER = 'counter';

    /**
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string;

    /**
     * Returns codename of MenuCounter provider (or null if not defined)
     *
     * @return string|null
     */
    public function getMenuCounterCodename(): ?string;

    /**
     * Returns sorted array of URL values for dynamic urls or numeric index for static urls
     * Returns empty array if no order is defined
     *
     * @return string[]
     */
    public function getMenuOrder(): array;
}
