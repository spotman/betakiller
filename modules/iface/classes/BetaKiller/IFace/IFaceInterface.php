<?php
namespace BetaKiller\IFace;

use BetaKiller\Url\IFaceModelInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IFaceInterface
{
    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array;

    /**
     * @param \DateTimeImmutable $lastModified
     *
     * @return $this
     */
    public function setLastModified(\DateTimeImmutable $lastModified): self;

    /**
     * @return \DateTimeImmutable
     */
    public function getLastModified(): \DateTimeImmutable;

    /**
     * @return \DateTimeImmutable
     */
    public function getDefaultLastModified(): \DateTimeImmutable;

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
     * @return \DateTimeImmutable
     */
    public function getExpiresDateTime(): \DateTimeImmutable;

    /**
     * @return int
     */
    public function getExpiresSeconds(): int;

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function before(ServerRequestInterface $request): void;

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
}
