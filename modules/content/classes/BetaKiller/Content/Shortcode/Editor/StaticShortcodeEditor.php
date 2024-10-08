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
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param string|null                                 $itemID
     *
     * @return array
     */
    public function getIndexIFaceData(?EntityModelInterface $relatedEntity, ?string $itemID): array
    {
        // Static shortcodes have no index data
        return [];
    }
}
