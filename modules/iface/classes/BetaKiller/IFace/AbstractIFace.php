<?php
namespace BetaKiller\IFace;

use BetaKiller\Url\IFaceModelInterface;
use DateInterval;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractIFace implements IFaceInterface
{
    /**
     * @var IFaceModelInterface
     */
    private $model;

    /**
     * @var \DateTimeImmutable|null
     */
    private $lastModified;

    /**
     * @var DateInterval|null
     */
    private $expiresInterval;

    /**
     * @return string
     */
    final public static function codename(): string
    {
        $codename = explode('\\', static::class);
        array_splice($codename, 0, -1 * \count($codename) + 2);
        $codename = implode('_', $codename);

        return $codename;
    }

    /**
     * @return string
     */
    final public function getCodename(): string
    {
        return $this->getModel()->getCodename();
    }

    /**
     * @param \DateTimeImmutable $lastModified
     *
     * @return $this
     */
    final public function setLastModified(\DateTimeImmutable $lastModified): IFaceInterface
    {
        // Check current last modified and update it if provided one is newer
        if ($this->lastModified && $this->lastModified > $lastModified) {
            return $this;
        }

        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    final public function getLastModified(): \DateTimeImmutable
    {
        return $this->lastModified ?: $this->getDefaultLastModified();
    }

    /**
     * @return \DateTimeImmutable
     */
    final public function getDefaultLastModified(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /**
     * @return DateInterval
     * @throws \Exception
     */
    public function getDefaultExpiresInterval(): DateInterval
    {
        return new \DateInterval('PT1H');
    }

    /**
     * @param \DateInterval $expires
     *
     * @return $this
     */
    final public function setExpiresInterval(DateInterval $expires): IFaceInterface
    {
        $this->expiresInterval = $expires;

        return $this;
    }

    /**
     * @return \DateInterval
     * @throws \Exception
     */
    final public function getExpiresInterval(): DateInterval
    {
        return $this->expiresInterval ?: $this->getDefaultExpiresInterval();
    }

    /**
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    final public function getExpiresDateTime(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->add($this->getExpiresInterval());
    }

    /**
     * @return int
     * @throws \Exception
     */
    final public function getExpiresSeconds(): int
    {
        $reference = new \DateTimeImmutable;
        $endTime   = $reference->add($this->getExpiresInterval());

        return $endTime->getTimestamp() - $reference->getTimestamp();
    }

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function before(ServerRequestInterface $request): void
    {
        // Empty by default
    }

    /**
     * This hook executed after real IFace processing only (on every request if IFace output was not cached)
     * Place here the code that needs to be executed only after real IFace processing (collect performance stat, etc)
     */
    public function after(): void
    {
        // Empty by default
    }

    /**
     * Getter for current iface model
     *
     * @return IFaceModelInterface
     */
    final public function getModel(): IFaceModelInterface
    {
        return $this->model;
    }

    /**
     * Setter for current iface model
     *
     * @param IFaceModelInterface $model
     *
     * @return $this
     */
    final public function setModel(IFaceModelInterface $model): IFaceInterface
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Use this method for disable HTTP caching
     *
     * @throws \Exception
     */
    protected function setExpiresInPast(): void
    {
        // No caching for admin zone
        $interval         = new \DateInterval('PT1H');
        $interval->invert = 1;

        $this->setExpiresInterval($interval);
    }
}
