<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\SeoMetaInterface;
use DateInterval;
use DateTimeInterface;

abstract class AbstractIFace implements IFaceInterface
{
    /**
     * @var IFaceModelInterface
     */
    private $model;

    /**
     * @var DateTimeInterface|null
     */
    private $lastModified;

    /**
     * @var DateInterval|null
     */
    private $expiresInterval;

    public function __construct()
    {
        // Empty by default, use __construct for defining concrete IFace dependencies
    }

    /**
     * @return string
     */
    final public function getCodename(): string
    {
        return $this->getModel()->getCodename();
    }

    /**
     * Returns plain label
     *
     * @return string
     */
    final public function getLabel(): string
    {
        return $this->getModel()->getLabel();
    }

    /**
     * Returns plain label
     *
     * @param string $value
     *
     * @return void
     */
    final public function setLabel(string $value): void
    {
        $this->getModel()->setLabel($value);
    }

    /**
     * Returns plain title
     *
     * @return string
     */
    final public function getTitle(): ?string
    {
        return $this->getModel()->getTitle();
    }

    /**
     * Returns plain description
     *
     * @return string
     */
    final public function getDescription(): ?string
    {
        return $this->getModel()->getDescription();
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     */
    final public function setTitle(string $value): SeoMetaInterface
    {
        $this->getModel()->setTitle($value);

        return $this;
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     */
    final public function setDescription(string $value): SeoMetaInterface
    {
        $this->getModel()->setDescription($value);

        return $this;
    }

    /**
     * @param \DateTimeInterface $lastModified
     *
     * @return $this
     */
    final public function setLastModified(\DateTimeInterface $lastModified): IFaceInterface
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    final public function getLastModified(): DateTimeInterface
    {
        return $this->lastModified ?: $this->getDefaultLastModified();
    }

    /**
     * @return \DateTimeInterface
     */
    final public function getDefaultLastModified(): DateTimeInterface
    {
        return new \DateTime();
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
     * @return \DateTimeInterface
     * @throws \Exception
     */
    final public function getExpiresDateTime(): DateTimeInterface
    {
        return (new \DateTime())->add($this->getExpiresInterval());
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
     */
    public function before(): void
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
}
