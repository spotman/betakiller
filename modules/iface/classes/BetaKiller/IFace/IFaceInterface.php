<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Url\UrlParametersInterface;

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
     * @param UrlParametersInterface|null $params
     *
     * @return string
     */
    public function getLabel(UrlParametersInterface $params = null): string;

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
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array;

    /**
     * @param \DateTime|NULL $last_modified
     *
     * @return $this
     */
    public function setLastModified(\DateTime $last_modified);

    /**
     * @return \DateTime
     */
    public function getLastModified(): \DateTime;

    /**
     * @return \DateTime
     */
    public function getDefaultLastModified(): \DateTime;

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
     * @return \DateTime
     */
    public function getExpiresDateTime(): \DateTime;

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
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $parameters
     *
     * @return bool
     */
    public function isCurrent(UrlParametersInterface $parameters = null): bool;

    /**
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $parameters
     * @param bool|null                                         $removeCyclingLinks
     * @param bool|null                                         $withDomain
     *
     * @return string
     */
    public function url(
        ?UrlParametersInterface $parameters = null,
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
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $params
     * @param int|null                                     $limit
     *
     * @return string[]
     */
    public function getPublicAvailableUrls(UrlParametersInterface $params, ?int $limit = null): array;
}
