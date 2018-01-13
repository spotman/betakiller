<?php
namespace BetaKiller\Content\Shortcode\Editor;

use BetaKiller\Content\Shortcode\AbstractContentElementShortcode;
use BetaKiller\Content\Shortcode\ShortcodeException;
use BetaKiller\Content\Shortcode\ShortcodeInterface;
use BetaKiller\Model\EntityModelInterface;

class ContentElementShortcodeEditor extends AbstractShortcodeEditor
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

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
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getIndexIFaceData(?EntityModelInterface $relatedEntity, ?int $itemID): array
    {
        $shortcode = $this->shortcodeFacade->createFromEntity($this->shortcodeEntity);

        if (!$shortcode instanceof AbstractContentElementShortcode) {
            throw new ShortcodeException('Shortcode must b instance of :must, :real given', [
                ':must' => AbstractContentElementShortcode::class,
                ':real' => \get_class($shortcode),
            ]);
        }

        $baseEditUrl   = $this->ifaceHelper->getUpdateEntityUrl($this->shortcodeEntity);
        $baseDeleteUrl = $this->ifaceHelper->getDeleteEntityUrl($this->shortcodeEntity);

        $items = [];

        foreach ($shortcode->getEditorListingItems($relatedEntity, $itemID) as $item) {
            $items[] = [
                'is_valid'   => $item->isValid(),
                'image'      => $item->getImageUrl(),
                'edit_url'   => $baseEditUrl.'?id='.$item->getId(),
                'delete_url' => $baseDeleteUrl.'?id='.$item->getId(),
            ];
        }

        return [
            'entity' => $relatedEntity ? $relatedEntity->getLabel() : null,
            'id'     => $itemID,
            'items'  => $items,
        ];
    }

    /**
     * Returns data for EditItem IFace
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeInterface $shortcode
     *
     * @return array
     */
    public function getEditIFaceData(ShortcodeInterface $shortcode): array
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
