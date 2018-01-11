<?php
namespace BetaKiller\Content\Shortcode\Editor;


use BetaKiller\Model\EntityModelInterface;

class StaticShortcodeEditor extends AbstractShortcodeEditor
{
    /**
     * Returns short name of related template
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'Static';
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
        // Static shortcodes have no index data
        return [];
    }

    /**
     * Returns data for EditItem IFace
     *
     * @return array
     */
    public function getEditIFaceData(): array
    {
        // Static shortcodes are not editable
        return [];
    }

    /**
     * Returns data for DeleteItem IFace
     *
     * @return array
     */
    public function getDeleteIFaceData(): array
    {
        // Static shortcodes are not deletable
        return [];
    }
}
