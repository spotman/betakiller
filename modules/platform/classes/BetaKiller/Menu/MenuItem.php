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
     * @var int|null
     */
    private $counter;

    /**
     * @var array
     */
    private $children = [];

    /**
     * @var string
     */
    private $codename;

    /**
     * MenuItem constructor.
     *
     * @param string   $url
     * @param string   $label
     * @param bool     $active
     * @param string   $codename
     * @param int|null $counter
     */
    public function __construct(string $url, string $label, bool $active, string $codename, ?int $counter = null)
    {
        $this->url      = $url;
        $this->label    = $label;
        $this->active   = $active;
        $this->codename = $codename;
        $this->counter  = $counter;
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
     * @return string
     */
    public function getCodename(): string
    {
        return $this->codename;
    }

    /**
     * @return int|null
     */
    public function getCounter(): ?int
    {
        return $this->counter;
    }

    /**
     * @param MenuItem[] $children
     */
    public function addChildren(array $children): void
    {
        $this->children = array_merge($this->children, $children);
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
            'name'     => $this->getCodename(),
            'children' => array_map(static function (MenuItem $item) {
                return $item->jsonSerialize();
            }, $this->getChildren()),
        ];
    }
}
