<?php
namespace BetaKiller\Assets\Model;

interface AssetsModelImageInterface extends HasPreviewAssetsModelInterface
{
    /**
     * @return int
     */
    public function getWidth(): int;

    /**
     * @return int
     */
    public function getHeight(): int;

    /**
     * @param int $value
     */
    public function setWidth(int $value): void;

    /**
     * @param int $value
     */
    public function setHeight(int $value): void;

    /**
     * @param string $value
     */
    public function setAlt(string $value): void;

    /**
     * @return string
     */
    public function getAlt(): string;

    /**
     * @param string $value
     */
    public function setTitle(string $value): void;

    /**
     * @return string
     */
    public function getTitle(): string;
}
