<?php
namespace BetaKiller\Model;

use BetaKiller\Helper\SeoMetaInterface;

/**
 * Interface ContentCategoryInterface
 *
 * @package BetaKiller\Content
 */
interface ContentCategoryInterface extends
    DispatchableEntityInterface,
    SeoMetaInterface,
    EntityHasWordpressIdInterface,
    SingleParentTreeModelInterface,
    HasLabelInterface
{
    /**
     * @param string $value
     */
    public function setUri(string $value): void;

    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * @param string $value
     */
    public function setLabel(string $value): void;

    public function isActive(): bool;

    public function linkPosts(array $item_ids): void;
}
