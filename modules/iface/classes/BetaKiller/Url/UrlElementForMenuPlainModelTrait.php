<?php
declare(strict_types=1);

namespace BetaKiller\Url;

trait UrlElementForMenuPlainModelTrait
{
    /**
     * @var string|null
     */
    private ?string $menuName = null;

    /**
     * @var string|null
     */
    private ?string $menuCounter = null;

    /**
     * @var string[]
     */
    private array $menuOrder = [];

    /**
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string
    {
        return $this->menuName;
    }

    /**
     * Returns sorted array of URL values for dynamic urls
     * Returns empty array if no order is defined
     *
     * @return string[]
     */
    public function getMenuOrder(): array
    {
        return $this->menuOrder;
    }

    /**
     * Returns codename of MenuCounter provider (or null if not defined)
     *
     * @return string|null
     */
    public function getMenuCounterCodename(): ?string
    {
        return $this->menuCounter;
    }

    protected function menuFromArray(array $data): void
    {
        if (isset($data[self::OPTION_MENU_NAME])) {
            $this->menuName = mb_strtolower($data[self::OPTION_MENU_NAME]);
        }

        if (isset($data[self::OPTION_MENU_COUNTER])) {
            $this->menuCounter = $data[self::OPTION_MENU_COUNTER];
        }

        if (isset($data[self::OPTION_MENU_ORDER])) {
            $orderedValues = explode(',', $data[self::OPTION_MENU_ORDER]);

            $this->menuOrder = array_filter(array_map('trim', $orderedValues));
        }
    }

    protected function menuToArray(): array
    {
        return [
            self::OPTION_MENU_NAME    => $this->getMenuName(),
            self::OPTION_MENU_ORDER   => implode(',', $this->getMenuOrder()),
            self::OPTION_MENU_COUNTER => $this->getMenuCounterCodename(),
        ];
    }
}
