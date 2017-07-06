<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;
use BetaKiller\Repository\ContentYoutubeRecordRepository;

class Widget_CustomTag_Youtube extends AbstractBaseWidget
{
    /**
     * @Inject
     * @var ContentYoutubeRecordRepository
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

        $videoID = (int)$context['id'];

        if (!$videoID) {
            throw new WidgetException('No YouTube ID provided');
        }

        /** @var \BetaKiller\Model\ContentYoutubeRecord $model */
        $model = $this->repository->findById($videoID);

//        $title  = Arr::get($context, 'title');
//        $align  = Arr::get($context, 'align', 'alignnone');
//        $alt    = Arr::get($context, 'alt');
//        $class  = Arr::get($context, 'class');
        $width  = (int)Arr::get($context, 'width');
        $height = (int)Arr::get($context, 'height');

//        $classes = array_filter(explode(' ', $class));
//        $classes['align'] = $align;
//
//        $attributes = [
//            'id'        =>  'admin-image-'.$model->getID(),
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
                'src'    => $model->getYoutubeEmbedUrl(),
                'width'  => $width,
                'height' => $height,
            ],
        ];
    }
}
