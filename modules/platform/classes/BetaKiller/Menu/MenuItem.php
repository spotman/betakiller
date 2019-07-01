<?php
declare(strict_types=1);

namespace BetaKiller\Menu;

use JsonSerializable;

class MenuItem implements JsonSerializable
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $label;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var int
     */
    private $counter;

    /**
     * @var array
     */
    private $children = [];

    /**
     * MenuItem constructor.
     *
     * @param string   $url
     * @param string   $label
     * @param bool     $active
     * @param int|null $counter
     */
    public function __construct(string $url, string $label, bool $active, int $counter = null)
    {
        $this->url     = $url;
        $this->label   = $label;
        $this->active  = $active;
        $this->counter = $counter ?? 0;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param MenuItem[] $children
     */
    public function addChildren(array $children): void
    {
        $this->children = array_merge($this->children, $children);
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->counter;
    }

    /**
     * @return MenuItem[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'url'      => $this->getUrl(),
            'label'    => $this->getLabel(),
            'active'   => $this->isActive(),
            'counter'  => $this->getCounter(),
            'children' => array_map(static function (MenuItem $item) {
                return $item->jsonSerialize();
            }, $this->getChildren()),
        ];
    }
}
