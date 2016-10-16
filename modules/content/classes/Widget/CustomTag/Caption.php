<?php

use BetaKiller\IFace\Widget;

class Widget_CustomTag_Caption extends Widget
{
    use BetaKiller\Helper\Content;

    /**
     * Returns data for View rendering
     *
     * @throws Widget\Exception
     * @return array
     */
    public function get_data()
    {
        $context = $this->getContext();

        $image_id = (int) $context['id'];

        if (!$image_id)
            throw new Widget\Exception('No image ID provided');

        $title = $context['title'];
        $align = Arr::get($context, 'align', 'alignnone');
        $class = Arr::get($context, 'class');
        $width = (int) Arr::get($context, 'width');

        $model = $this->model_factory_content_image_element()->get_by_id($image_id);

        return [
            'image'     =>  $model->get_arguments_for_img_tag($model->get_original_url()),
            'caption'   =>  $title,
            'align'     =>  $align,
            'class'     =>  $class,
            'width'     =>  $width,
        ];
    }
}
