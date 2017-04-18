<?php

use BetaKiller\IFace\Widget\BaseWidget;

class Widget_CustomTag_Caption extends BaseWidget
{
    use BetaKiller\Helper\ContentTrait;

    /**
     * Returns data for View rendering
     *
     * @throws Widget\WidgetException
     * @return array
     */
    public function getData()
    {
        $context = $this->getContext();

        $image_id = (int) $context['id'];

        if (!$image_id)
            throw new Widget\WidgetException('No image ID provided');

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
            'image'     =>  $model->get_attributes_for_img_tag($model::SIZE_ORIGINAL),
            'caption'   =>  $title,
            'align'     =>  $align,
            'class'     =>  $class,
            'width'     =>  $width,
        ];
    }
}
