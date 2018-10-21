<?php
namespace BetaKiller\Content\Shortcode\Editor;

use BetaKiller\Assets\ContentTypes;
use BetaKiller\Content\Shortcode\ContentElementShortcodeInterface;
use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\Content\Shortcode\ShortcodeException;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Model\EntityModelInterface;

class ContentElementShortcodeEditor extends AbstractShortcodeEditor
{
    public const ANY_MIME_TYPES = '*/*';

    /**
     * @var \BetaKiller\Assets\ContentTypes
     */
    private $contentTypes;

    /**
     * @var ShortcodeEntityInterface
     */
    private $shortcodeEntity;

    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    private $shortcodeFacade;

    /**
     * ContentElementShortcodeEditor constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade          $facade
     * @param \BetaKiller\Assets\ContentTypes                        $contentTypes
     */
    public function __construct(ShortcodeEntityInterface $entity, ShortcodeFacade $facade, ContentTypes $contentTypes)
    {
        $this->shortcodeEntity = $entity;
        $this->shortcodeFacade = $facade;
        $this->contentTypes    = $contentTypes;
    }

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
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getIndexIFaceData(?EntityModelInterface $relatedEntity, ?int $itemID): array
    {
        $shortcode = $this->shortcodeFacade->createFromEntity($this->shortcodeEntity);

        if (!$shortcode instanceof ContentElementShortcodeInterface) {
            throw new ShortcodeException('Shortcode must b instance of :must, :real given', [
                ':must' => ContentElementShortcodeInterface::class,
                ':real' => \get_class($shortcode),
            ]);
        }

        $mimeTypes  = $shortcode->getEditorItemAllowedMimeTypes();
        $extensions = [];

        // Extensions from mime-types
        foreach ($mimeTypes as $mimeType) {
            if ($mimeType === self::ANY_MIME_TYPES) {
                $extensions[] = '*';
            } else {
                foreach ($this->contentTypes->getExtensions($mimeType) as $ext) {
                    $extensions[] = $ext;
                }
            }
        }

        return [
            'codename'       => $shortcode->getCodename(),
            'tag_name'       => $shortcode->getTagName(),
            'entity_slug'    => $relatedEntity ? $relatedEntity->getSlug() : null,
            'entity_item_id' => $itemID,
            'upload_url'     => $shortcode->getEditorItemUploadUrl(),
            'mime_types'     => $mimeTypes,
            'extensions'     => $extensions,
        ];
    }
}
