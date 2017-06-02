<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_CustomTag_Photo extends AbstractBaseWidget
{
    use BetaKiller\Helper\ContentTrait;

    /**
     * Returns data for View rendering
     *
     * @throws \BetaKiller\IFace\Widget\WidgetException
     * @return array
     */
    public function getData()
    {
        $image_id = (int)$this->getContextParam('id');

        if (!$image_id) {
            throw new WidgetException('No image ID provided');
        }

        $model = $this->model_factory_content_image_element()->get_by_id($image_id);

        $title    = $this->getContextParam('title');
        $align    = $this->getContextParam('align', 'alignnone');
        $alt      = $this->getContextParam('alt');
        $class    = $this->getContextParam('class');
        $width    = (int)$this->getContextParam('width');
        $zoomable = ($this->getContextParam(CustomTag::PHOTO_ZOOMABLE) === CustomTag::PHOTO_ZOOMABLE_ENABLED);

        if (strpos($class, 'align') === false) {
            $classes[] = $align;
        }

        $classes = array_filter(explode(' ', $class));

        $attributes = [
            'id'    => 'content-image-'.$model->get_id(),
            'title' => $title ?: $model->getTitle(),
            'alt'   => $alt ?: $model->getAlt(),
            'class' => implode(' ', array_unique($classes)),
        ];

        if ($width) {
            $attributes['style'] = 'width: '.$width.'px';
        }

        return [
            'zoomable' => $zoomable,
            'image'    => $model->getAttributesForImgTag($model::SIZE_ORIGINAL, $attributes),
        ];
    }
}
