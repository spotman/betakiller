<?php
namespace BetaKiller\IFace;

use BetaKiller\Url\AbstractUrlElementInstance;
use BetaKiller\Url\IFaceModelInterface;
use DateInterval;
use DateTimeImmutable;

abstract class AbstractIFace extends AbstractUrlElementInstance implements IFaceInterface
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
     * @var bool
     */
    private $isHttpCacheEnabled = false;

    /**
     * @return string
     */
    final public static function getSuffix(): string
    {
        return self::SUFFIX;
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
    final protected function setLastModified(DateTimeImmutable $lastModified): IFaceInterface
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
    final public function getLastModified(): DateTimeImmutable
    {
        return $this->lastModified ?: $this->getDefaultLastModified();
    }

    /**
     * @param \DateInterval $expires
     *
     * @return $this
     */
    final protected function setExpiresInterval(DateInterval $expires): IFaceInterface
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
    final public function getExpiresDateTime(): DateTimeImmutable
    {
        return (new DateTimeImmutable())->add($this->getExpiresInterval());
    }

    /**
     * @return int
     * @throws \Exception
     */
    final public function getExpiresSeconds(): int
    {
        $reference = new DateTimeImmutable;
        $endTime   = $reference->add($this->getExpiresInterval());

        return $endTime->getTimestamp() - $reference->getTimestamp();
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
     * @return bool
     */
    final public function isHttpCachingEnabled(): bool
    {
        return $this->isHttpCacheEnabled;
    }
    /**
     * Use this method for enable HTTP caching
     *
     * @param \DateInterval|null $expiresIn
     *
     * @throws \Exception
     */
    final protected function enableHttpCache(DateInterval $expiresIn = null): void
    {
        $this->isHttpCacheEnabled = true;
        $this->setExpiresInterval($expiresIn ?? $this->getOneHourExpiresInterval());
    }

    /**
     * @return DateInterval
     * @throws \Exception
     */
    private function getDefaultExpiresInterval(): DateInterval
    {
        $interval         = new DateInterval('PT1H');
        $interval->invert = 1;

        // Expires in past, no caching
        return $interval;
    }

    private function getOneHourExpiresInterval(): DateInterval
    {
        return new DateInterval('PT1H'); // 1 hour caching
    }

    /**
     * @return \DateTimeImmutable
     */
    private function getDefaultLastModified(): DateTimeImmutable
    {
        return new DateTimeImmutable(); // Now
    }
}
