<?php
namespace BetaKiller\Content\Shortcode\Editor;

use BetaKiller\Model\EntityModelInterface;

class ContentElementShortcodeEditor extends AbstractShortcodeEditor
{
    /**
     * Returns short name of related template
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'ContentElement';
    }

    /**
     * Returns data for IndexItem IFace
     *
     * @param \BetaKiller\Model\EntityModelInterface|null $entity
     * @param int|null                                    $itemID
     *
     * @return array
     */
    public function getIndexIFaceData(?EntityModelInterface $entity, ?int $itemID): array
    {
        return [
            'entity' => $entity ? $entity->getLabel() : null,
            'id'     => $itemID,
        ];
    }

    /**
     * Returns data for EditItem IFace
     *
     * @return array
     */
    public function getEditIFaceData(): array
    {
        return [];
    }

    /**
     * Returns data for DeleteItem IFace
     *
     * @return array
     */
    public function getDeleteIFaceData(): array
    {
        return [];
    }
}
