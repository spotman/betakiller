<?php
namespace BetaKiller\Content\CustomTag;

//use BetaKiller\Helper\AssetsHelper;
//use BetaKiller\Repository\ContentAttachmentRepository;

class AttachmentCustomTag extends AbstractCustomTag
{
//    /**
//     * @var \BetaKiller\Repository\ContentAttachmentRepository
//     */
//    private $attachmentRepository;
//
//    /**
//     * @var \BetaKiller\Helper\AssetsHelper
//     */
//    private $assetsHelper;
//
//    /**
//     * PhotoCustomTag constructor.
//     *
//     * @param \BetaKiller\Repository\ContentAttachmentRepository $repository
//     * @param \BetaKiller\Helper\AssetsHelper               $helper
//     */
//    public function __construct(ContentAttachmentRepository $repository, AssetsHelper $helper)
//    {
//        $this->attachmentRepository = $repository;
//        $this->assetsHelper         = $helper;
//    }

    const TAG_NAME = 'attachment';

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string
    {
        return self::TAG_NAME;
    }

    public function getWysiwygPluginPreviewSrc(array $attributes): string
    {
//        $id = (int)$attributes['id'];
//        $model = $this->attachmentRepository->findById($id);

        // TODO Show button or link (depends on attributes)
        return '/assets/static/images/download-button.png';
    }
}
