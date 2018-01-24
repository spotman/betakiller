<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Model\HasLabelInterface;
use BetaKiller\Url\UrlContainerInterface;

interface IFaceInterface extends SeoMetaInterface, HasLabelInterface
{
    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * Returns data for View
     *
     * @return array
     */
    public function getData(): array;

    /**
     * @param \DateTimeInterface $lastModified
     *
     * @return $this
     */
    public function setLastModified(\DateTimeInterface $lastModified): self;

    /**
     * @return \DateTimeInterface
     */
    public function getLastModified(): \DateTimeInterface;

    /**
     * @return \DateTimeInterface
     */
    public function getDefaultLastModified(): \DateTimeInterface;

    /**
     * @return \DateInterval
     */
    public function getDefaultExpiresInterval(): \DateInterval;

    /**
     * @param \DateInterval|NULL $expires
     *
     * @return $this
     */
    public function setExpiresInterval(\DateInterval $expires): self;

    /**
     * @return \DateInterval
     */
    public function getExpiresInterval(): \DateInterval;

    /**
     * @return \DateTimeInterface
     */
    public function getExpiresDateTime(): \DateTimeInterface;

    /**
     * @return int
     */
    public function getExpiresSeconds(): int;

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before(): void;

    /**
     * This hook executed after real IFace processing only (on every request if IFace output was not cached)
     * Place here the code that needs to be executed only after real IFace processing (collect performance stat, etc)
     */
    public function after(): void;

    /**
     * Getter for current iface model
     *
     * @return IFaceModelInterface
     */
    public function getModel(): IFaceModelInterface;

    /**
     * Setter for current iface model
     *
     * @param IFaceModelInterface $model
     *
     * @return $this
     */
    public function setModel(IFaceModelInterface $model): self;

    /**
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     * @param bool|null                                  $removeCyclingLinks
     *
     * @return string
     * @deprecated Use IFaceHelper or UrlHelper instead
     */
    public function url(?UrlContainerInterface $params = null, ?bool $removeCyclingLinks = null): string;
}
