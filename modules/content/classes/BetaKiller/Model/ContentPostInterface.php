<?php
namespace BetaKiller\Model;

use BetaKiller\Helper\SeoMetaInterface;
use Spotman\Api\ApiResponseItemInterface;

interface ContentPostInterface extends
    DispatchableEntityInterface,
    RelatedEntityInterface,
    ApiResponseItemInterface,
    EntityHasWordpressIdInterface,
    HasWorkflowStateInterface,
    ModelWithRevisionsInterface,
    EntityWithAclSpecInterface,
    SeoMetaInterface,
    HasPublicZoneAccessSpecInterface
{
    /**
     * @return int
     */
    public function getType(): int;

    public static function getPrioritizedTypesList(): array;

    public function markAsPage(): self;

    public function markAsArticle(): self;

    public function isPage(): bool;

    public function isArticle(): bool;

    /**
     * @param \BetaKiller\Model\ContentCategoryInterface $value
     *
     */
    public function setCategory(ContentCategoryInterface $value): void;

    /**
     * @return \BetaKiller\Model\ContentCategoryInterface
     */
    public function getCategory(): ?ContentCategoryInterface;

    public function needsCategory(): bool;

    public function needsThumbnails(): bool;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setUri(string $value): self;

    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\ContentPostInterface
     */
    public function setContent(string $value): self;

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @param \DateTimeImmutable $value
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeImmutable $value): self;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable;

    /**
     * @param \DateTimeImmutable $value
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTimeImmutable $value): self;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return $this
     */
    public function setCreatedBy(UserInterface $user): self;

    /**
     * @return UserInterface
     */
    public function getCreatedBy(): UserInterface;

    /**
     * @return $this
     */
    public function incrementViewsCount(): self;

    /**
     * @return int
     */
    public function getViewsCount(): int;

    /**
     * @return \BetaKiller\Model\ContentPostThumbnailInterface[]
     */
    public function getThumbnails(): array;

    /**
     * @return \BetaKiller\Model\ContentPostThumbnailInterface
     */
    public function getFirstThumbnail(): ContentPostThumbnailInterface;

    /**
     * @return bool
     */
    public function isDefault(): bool;
}
