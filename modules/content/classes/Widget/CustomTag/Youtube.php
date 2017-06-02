<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_CustomTag_Youtube extends AbstractBaseWidget
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
        $context = $this->getContext();

        $video_id = (int) $context['id'];

        if (!$video_id)
            throw new WidgetException('No YouTube ID provided');

        $model = $this->model_factory_content_youtube_record()->get_by_id($video_id);

//        $title  = Arr::get($context, 'title');
//        $align  = Arr::get($context, 'align', 'alignnone');
//        $alt    = Arr::get($context, 'alt');
//        $class  = Arr::get($context, 'class');
        $width  = (int) Arr::get($context, 'width');
        $height  = (int) Arr::get($context, 'height');

//        $classes = array_filter(explode(' ', $class));
//        $classes['align'] = $align;
//
//        $attributes = [
//            'id'        =>  'admin-image-'.$model->get_id(),
//            'title' =>  $title ?: $model->get_title(),
//            'alt'   =>  $alt ?: $model->getAlt(),
//            'class' =>  implode(' ', array_unique($classes)),
//        ];
//
//        if ($width) {
//            $attributes['style'] = 'width: '.$width.'px';
//        }

        return [
            'video' => [
                // TODO
                'src'       =>  $model->get_youtube_embed_url(),
                'width'     =>  $width,
                'height'    =>  $height,
            ],
        ];
    }
}
