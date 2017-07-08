<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_CustomTag_Attachment extends AbstractBaseWidget
{
    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentAttachmentRepository
     */
    private $repository;

    /**
     * Returns data for View rendering
     *
     * @throws \BetaKiller\IFace\Widget\WidgetException
     * @return array
     */
    public function getData(): array
    {
        $context = $this->getContext();

        $attachID = (int)$context['id'];

        if (!$attachID) {
            throw new WidgetException('No attachment ID provided');
        }

        $model = $this->repository->findById($attachID);

        if (!$model) {
            throw new WidgetException('No content attachment found for ID :value', [':value' => $attachID]);
        }

        $title = HTML::chars(Arr::get($context, 'title'));
        $class = Arr::get($context, 'class');

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
