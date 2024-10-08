<?php
namespace BetaKiller\Content\Shortcode\Editor;

use BetaKiller\Model\EntityModelInterface;

interface ShortcodeEditorInterface
{
    public const CLASS_SUFFIX = 'ShortcodeEditor';

    /**
     * Returns short name of related template
     *
     * @return string
     */
    public function getTemplateName(): string;

    /**
     * Returns data for IndexItem IFace
     *
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param string|null                                 $itemID
     *
     * @return array
     */
    public function getIndexIFaceData(?EntityModelInterface $relatedEntity, ?string $itemID): array;
}
