<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Model\EntityModelInterface;

interface ItemBasedShortcodeInterface extends ShortcodeInterface
{
    /**
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return \BetaKiller\Content\Shortcode\Editor\EditorListingItem[]
     */
    public function getEditorListingItems(?EntityModelInterface $relatedEntity, ?int $itemID): array;
}
