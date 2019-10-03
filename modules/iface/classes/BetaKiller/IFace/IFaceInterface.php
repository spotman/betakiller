<?php
namespace BetaKiller\IFace;

use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInstanceInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IFaceInterface extends UrlElementInstanceInterface
{
    public const NAMESPACE = 'IFace';
    public const SUFFIX    = 'IFace';

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
     * @return \DateTimeImmutable
     */
    public function getLastModified(): \DateTimeImmutable;

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
     * @return bool
     */
    public function isHttpCachingEnabled(): bool;
}
