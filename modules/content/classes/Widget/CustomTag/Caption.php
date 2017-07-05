<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_CustomTag_Caption extends AbstractBaseWidget
{
    use BetaKiller\Helper\ContentTrait;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * Returns data for View rendering
     *
     * @throws \BetaKiller\IFace\Widget\WidgetException
     * @return array
     */
    public function getData(): array
    {
        $context = $this->getContext();

        $image_id = (int) $context['id'];

        if (!$image_id) {
            throw new WidgetException('No image ID provided');
        }

        $title = $context['title'];
        $align = Arr::get($context, 'align', 'alignnone');
        $class = Arr::get($context, 'class');
        $width = (int) Arr::get($context, 'width');

        if (strpos($class, 'align') === FALSE)
        {
            $class .= ' '.$align;
        }

        $model = $this->model_factory_content_image_element()->get_by_id($image_id);

        return [
            'image'     =>  $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_ORIGINAL),
            'caption'   =>  $title,
            'align'     =>  $align,
            'class'     =>  $class,
            'width'     =>  $width,
        ];
    }
}
