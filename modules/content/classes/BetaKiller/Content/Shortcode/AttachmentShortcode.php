<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Repository\ContentAttachmentRepository;

class AttachmentShortcode extends AbstractEditableShortcode
{
    /**
     * @var \BetaKiller\Repository\ContentAttachmentRepository
     */
    private $attachmentRepository;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * ImageShortcode constructor.
     *
     * @param \BetaKiller\Repository\ContentAttachmentRepository $repository
     * @param \BetaKiller\Helper\AssetsHelper                    $helper
     */
    public function __construct(ContentAttachmentRepository $repository, AssetsHelper $helper)
    {
        $this->attachmentRepository = $repository;
        $this->assetsHelper         = $helper;

        parent::__construct('attachment');
    }

    /**
     * Returns true if current tag may have text content between open and closing markers
     *
     * @return bool
     */
    public function mayHaveContent(): bool
    {
        return false;
    }

    public function getWysiwygPluginPreviewSrc(): string
    {
//        $id = (int)$attributes['id'];
//        $model = $this->attachmentRepository->findById($id);

        // TODO Show button or link (depends on attributes)
        return '/assets/static/images/download-button.png';
    }

    /**
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $attachID = (int)$this->getAttribute('id');

        if (!$attachID) {
            throw new ShortcodeException('No attachment ID provided');
        }

        $model = $this->attachmentRepository->findById($attachID);

        if (!$model) {
            throw new ShortcodeException('No content attachment found for ID :value', [':value' => $attachID]);
        }

        $title = \HTML::chars($this->getAttribute('title'));
        $class = $this->getAttribute('class');

        $i18nParams = [
            ':name' => $model->getOriginalName(),
        ];

        return [
            'attachment' => [
                'url'   => $this->assetsHelper->getOriginalUrl($model),
                'title' => $title ?: __('custom_tag.attachment.title', $i18nParams),
                'alt'   => __('custom_tag.attachment.alt', $i18nParams),
                'class' => $class,
            ],
        ];
    }
}
