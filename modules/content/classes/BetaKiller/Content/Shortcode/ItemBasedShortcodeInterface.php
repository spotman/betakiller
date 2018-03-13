<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Model\EntityModelInterface;

interface ItemBasedShortcodeInterface extends ShortcodeInterface
{
    /**
     * @return int|null
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getID(): ?int;

    /**
     * @param int $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setID(int $value): void;

    /**
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return \BetaKiller\Content\Shortcode\Editor\EditorListingItem[]
     */
    public function getEditorListingItems(?EntityModelInterface $relatedEntity, ?int $itemID): array;

    /**
     * Returns item data (based on "id" attribute value)
     *
     * @return array
     */
    public function getEditorItemData(): array;

    /**
     * Update model data (based on "id" attribute value)
     *
     * @param array $data
     */
    public function updateEditorItemData(array $data): void;

    /**
     * Return url for uploading new items or null if items can not be uploaded and must be added via regular edit form
     *
     * @return null|string
     */
    public function getEditorItemUploadUrl(): ?string;

    /**
     * Return array of allowed mime-types
     *
     * @return string[]
     */
    public function getEditorItemAllowedMimeTypes(): array;
}
