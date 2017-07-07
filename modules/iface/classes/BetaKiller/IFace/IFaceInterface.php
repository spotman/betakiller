<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Url\UrlContainerInterface;

interface IFaceInterface extends SeoMetaInterface
{
    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * @return string
     */
    public function render(): string;

    /**
     * @return string
     */
    public function getLayoutCodename(): string;

    /**
     * Returns processed label
     *
     * @param UrlContainerInterface|null $params
     *
     * @return string
     */
    public function getLabel(UrlContainerInterface $params = null): string;

    /**
     * Returns label source/pattern
     *
     * @return string
     */
    public function getLabelSource(): string;

    /**
     * Returns title source/pattern
     *
     * @return string
     */
    public function getTitleSource(): ?string;

    /**
     * Returns description source/pattern
     *
     * @return string
     */
    public function getDescriptionSource(): ?string;

    /**
     * Returns data for View
     *
     * @return array
     */
    public function getData(): array;

    /**
     * @param \DateTimeInterface $last_modified
     *
     * @return $this
     */
    public function setLastModified(\DateTimeInterface $last_modified);

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
    public function setExpiresInterval(\DateInterval $expires);

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
     * @return string
     */
    public function __toString(): string;

    /**
     * @return IFaceInterface|null
     */
    public function getParent(): ?IFaceInterface;

    /**
     * @param \BetaKiller\IFace\IFaceInterface $parent
     *
     * @return $this
     */
    public function setParent(IFaceInterface $parent);

    /**
     * @return \BetaKiller\IFace\IFaceInterface[]
     */
    public function getChildren(): array;

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
    public function setModel(IFaceModelInterface $model);

    /**
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * @return bool
     */
    public function isInStack(): bool;

    /**
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $parameters
     *
     * @return bool
     */
    public function isCurrent(UrlContainerInterface $parameters = null): bool;

    /**
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $parameters
     * @param bool|null                                        $removeCyclingLinks
     * @param bool|null                                        $withDomain
     *
     * @return string
     */
    public function url(
        ?UrlContainerInterface $parameters = null,
        ?bool $removeCyclingLinks = null,
        ?bool $withDomain = null
    ): string;

    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * Returns zone codename where this IFace is placed
     *
     * @return string
     */
    public function getZoneName(): string;

    /**
     * Returns model name of the linked entity
     *
     * @return string
     */
    public function getEntityModelName(): ?string;

    /**
     * Returns entity [primary] action, applied by this IFace
     *
     * @return string
     */
    public function getEntityActionName(): ?string;

    /**
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array;

    /**
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $params
     *
     * @return string[]
     */
    public function getPublicAvailableUrls(UrlContainerInterface $params): array;
}
