<?php

use BetaKiller\IFace\Widget;

class Widget_CustomTag_Photo extends Widget
{
    use BetaKiller\Helper\ContentTrait;

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

        $model = $this->model_factory_content_image_element()->get_by_id($image_id);

        $title  = Arr::get($context, 'title');
        $align  = Arr::get($context, 'align', 'alignnone');
        $alt    = Arr::get($context, 'alt');
        $class  = Arr::get($context, 'class');
        $width  = (int) Arr::get($context, 'width');

        if (strpos($class, 'align') === FALSE)
        {
            $classes[] = $align;
        }

        $classes = array_filter(explode(' ', $class));

        $attributes = [
            'id'    =>  'content-image-'.$model->get_id(),
            'title' =>  $title ?: $model->get_title(),
            'alt'   =>  $alt ?: $model->get_alt(),
            'class' =>  implode(' ', array_unique($classes)),
        ];

        if ($width) {
            $attributes['style'] = 'width: '.$width.'px';
        }

        return [
            'image'     =>  $model->get_attributes_for_img_tag($model::SIZE_ORIGINAL, $attributes),
        ];
    }
}
