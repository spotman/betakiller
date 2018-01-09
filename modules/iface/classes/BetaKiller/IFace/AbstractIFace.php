<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\Model\IFaceZone;
use DateInterval;
use DateTimeInterface;

abstract class AbstractIFace implements IFaceInterface
{
    /**
     * @var IFaceModelInterface
     */
    private $model;

    /**
     * @var IFaceInterface|null Parent iface
     */
    private $parent;

    /**
     * @var DateTimeInterface|null
     */
    private $lastModified;

    /**
     * @var DateInterval|null
     */
    private $expiresInterval;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    protected $ifaceHelper;

    public function __construct(IFaceHelper $ifaceHelper)
    {
        $this->ifaceHelper = $ifaceHelper;
    }

    /**
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getCodename(): string
    {
        return $this->getModel()->getCodename();
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return $this->ifaceHelper->renderIFace($this);
    }

    /**
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getLayoutCodename(): ?string
    {
        $current = $this;

        // Climb up the IFace tree for a layout codename
        do {
            $layoutCodename = $current->getModel()->getLayoutCodename();
        } while (!$layoutCodename && $current = $current->getParent());

        return $layoutCodename;
    }

    /**
     * Returns plain label
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getLabel(): string
    {
        return $this->getModel()->getLabel();
    }

    /**
     * Returns plain title
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getTitle(): ?string
    {
        return $this->getModel()->getTitle();
    }

    /**
     * Returns plain description
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDescription(): ?string
    {
        return $this->getModel()->getDescription();
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function setTitle(string $value): SeoMetaInterface
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function setDescription(string $value): SeoMetaInterface
    {
        $this->getModel()->setDescription($value);

        return $this;
    }

    /**
     * @param \DateTimeInterface $lastModified
     *
     * @return $this
     */
    public function setLastModified(\DateTimeInterface $lastModified): IFaceInterface
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModified(): DateTimeInterface
    {
        return $this->lastModified ?: $this->getDefaultLastModified();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDefaultLastModified(): DateTimeInterface
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
    public function setExpiresInterval(DateInterval $expires): IFaceInterface
    {
        $this->expiresInterval = $expires;

        return $this;
    }

    /**
     * @return \DateInterval
     * @throws \Exception
     */
    public function getExpiresInterval(): DateInterval
    {
        return $this->expiresInterval ?: $this->getDefaultExpiresInterval();
    }

    /**
     * @return \DateTimeInterface
     * @throws \Exception
     */
    public function getExpiresDateTime(): DateTimeInterface
    {
        return (new \DateTime())->add($this->getExpiresInterval());
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getExpiresSeconds(): int
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

    public function __toString(): string
    {
        return (string)$this->render();
    }

    /**
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function getParent(): ?IFaceInterface
    {
        if (!$this->parent) {
            $this->parent = $this->ifaceHelper->getIFaceParent($this);
        }

        return $this->parent;
    }

    /**
     * Getter for current iface model
     *
     * @return IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getModel(): IFaceModelInterface
    {
        if (!$this->model) {
            throw new IFaceException('Model required for IFace');
        }

        return $this->model;
    }

    /**
     * Setter for current iface model
     *
     * @param IFaceModelInterface $model
     *
     * @return $this
     */
    public function setModel(IFaceModelInterface $model): IFaceInterface
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return bool
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function isDefault(): bool
    {
        return $this->getModel()->isDefault();
    }

    /**
     * Returns model name of the linked entity
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getEntityModelName(): ?string
    {
        return $this->getModel()->getEntityModelName();
    }

    /**
     * Returns entity [primary] action, applied by this IFace
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getEntityActionName(): ?string
    {
        return $this->getModel()->getEntityActionName();
    }

    /**
     * Returns zone codename where this IFace is placed
     * Inherits zone from parent iface
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getZoneName(): string
    {
        $current = $this;

        do {
            $zoneName = $current->getModel()->getZoneName();
        } while (!$zoneName && $current = $current->getParent());

        // Public zone by default
        return $zoneName ?: IFaceZone::PUBLIC_ZONE;
    }

    /**
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getAdditionalAclRules(): array
    {
        return $this->getModel()->getAdditionalAclRules();
    }

    /**
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $urlContainer
     * @param bool|null                                        $removeCyclingLinks
     * @param bool|null                                        $withDomain
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function url(
        UrlContainerInterface $urlContainer = null,
        ?bool $removeCyclingLinks = null,
        ?bool $withDomain = null
    ): string {
        return $this->ifaceHelper->makeUrl($this, $urlContainer, $removeCyclingLinks, $withDomain);
    }

    /**
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getUri(): string
    {
        return $this->getModel()->getUri();
    }
}
