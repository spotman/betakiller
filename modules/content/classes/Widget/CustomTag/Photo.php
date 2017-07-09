<?php

use BetaKiller\Content\CustomTag\PhotoCustomTag;
use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_CustomTag_Photo extends AbstractBaseWidget
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
        $imageID = (int)$this->getContextParam('id');

        if (!$imageID) {
            throw new WidgetException('No image ID provided');
        }

        $model = $this->repository->findById($imageID);

        if (!$model) {
            throw new WidgetException('No image found for ID :id', [':id' => $imageID]);
        }

        $title    = $this->getContextParam('title');
        $align    = $this->getContextParam('align', 'alignnone');
        $alt      = $this->getContextParam('alt');
        $class    = $this->getContextParam('class');
        $width    = (int)$this->getContextParam('width');
        $zoomable = ($this->getContextParam(PhotoCustomTag::ATTRIBUTE_ZOOMABLE_NAME) === PhotoCustomTag::ATTRIBUTE_ZOOMABLE_ENABLED);

        if (strpos($class, 'align') === false) {
            $classes[] = $align;
        }

        $classes = array_filter(explode(' ', $class));

        $attributes = [
            'id'    => 'content-image-'.$model->getID(),
            'title' => $title ?: $model->getTitle(),
            'alt'   => $alt ?: $model->getAlt(),
            'class' => implode(' ', array_unique($classes)),
        ];

        if ($width) {
            $attributes['style'] = 'width: '.$width.'px';
        }

        return [
            'zoomable' => $zoomable,
            'image'    => $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_ORIGINAL, $attributes),
        ];
    }
}
