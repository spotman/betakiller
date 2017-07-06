<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_CustomTag_Caption extends AbstractBaseWidget
{
    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentImageRepository
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

        $imageID = (int)$context['id'];

        if (!$imageID) {
            throw new WidgetException('No image ID provided');
        }

        $title = $context['title'];
        $align = Arr::get($context, 'align', 'alignnone');
        $class = Arr::get($context, 'class');
        $width = (int)Arr::get($context, 'width');

        if (strpos($class, 'align') === false) {
            $class .= ' '.$align;
        }

        /** @var \BetaKiller\Model\ContentImage $model */
        $model = $this->repository->findById($imageID);

        return [
            'image'   => $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_ORIGINAL),
            'caption' => $title,
            'align'   => $align,
            'class'   => $class,
            'width'   => $width,
        ];
    }
}
