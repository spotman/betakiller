<?php
namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AssetsModelImageInterface;

abstract class AbstractOrmBasedAssetsImageModel extends AbstractOrmBasedAssetsModel implements AssetsModelImageInterface
{
    /**
     * @return int
     */
    public function getWidth(): int
    {
        return (int)$this->get('width');
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return (int)$this->get('height');
    }

    /**
     * @param int $value
     */
    public function setWidth(int $value): void
    {
        $this->set('width', $value);
    }

    /**
     * @param int $value
     */
    public function setHeight(int $value): void
    {
        $this->set('height', $value);
    }

    /**
     * @param string $value
     */
    public function setAlt(string $value): void
    {
        $this->set('alt', $value);
    }

    /**
     * @return string
     */
    public function getAlt(): string
    {
        return (string)$this->get('alt');
    }

    /**
     * @param string $value
     */
    public function setTitle(string $value): void
    {
        $this->set('title', $value);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return (string)$this->get('title');
    }

    /**
     * @return array
     */
    public function getDefaultAttributesForImgTag(): array
    {
        // Empty by default
        return [
            'alt'   => $this->getAlt(),
            'title' => $this->getTitle(),
        ];
    }
}
