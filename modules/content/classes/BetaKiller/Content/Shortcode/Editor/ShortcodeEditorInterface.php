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
     * @param \BetaKiller\Model\EntityModelInterface|null $entity
     * @param int|null                                                       $itemID
     *
     * @return array
     */
    public function getIndexIFaceData(?EntityModelInterface $entity, ?int $itemID): array;

    /**
     * Returns data for EditItem IFace
     *
     * @return array
     */
    public function getEditIFaceData(): array;

    /**
     * Returns data for DeleteItem IFace
     *
     * @return array
     */
    public function getDeleteIFaceData(): array;
}
