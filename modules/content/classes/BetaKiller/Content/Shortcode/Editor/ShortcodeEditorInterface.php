<?php
namespace BetaKiller\Content\Shortcode\Editor;

use BetaKiller\Content\Shortcode\ShortcodeInterface;
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
     * @param int|null                                    $itemID
     *
     * @return array
     */
    public function getIndexIFaceData(?EntityModelInterface $relatedEntity, ?int $itemID): array;

    /**
     * Returns data for EditItem IFace
     *
     * @param ShortcodeInterface $shortcode
     *
     * @return array
     */
    public function getEditIFaceData(ShortcodeInterface $shortcode): array;

    /**
     * Returns data for DeleteItem IFace
     *
     * @return array
     */
    public function getDeleteIFaceData(): array;
}
