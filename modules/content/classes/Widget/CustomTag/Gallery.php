<?php

use BetaKiller\IFace\Widget;

class Widget_CustomTag_Gallery extends Widget
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

        $image_ids = explode(',', $context['ids']);

        if (!$image_ids)
            throw new Widget\Exception('No image IDs provided');

        // TODO move to tag-related class CustomTag_Gallery
        $allowed_types = [
            'masonry',
            'slider'
        ];

        $type = Arr::get($context, 'type', $allowed_types[0]);

        if (!in_array($type, $allowed_types))
            throw new Widget\Exception('Unknown gallery type :value', [':value' => $type]);

        $columns = (int) Arr::get($context, 'columns', 3);

        $images = [];

        foreach ($image_ids as $id)
        {
            $model = $this->model_factory_content_image_element($id);

            if (!$model->loaded())
                continue;

            $images[] = $model->get_arguments_for_img_tag($model::SIZE_PREVIEW);
        }

        if (!$images)
        {
            $this->warning('Gallery has no images for ids [:ids]', [':ids' => implode(', ', $image_ids)]);
        }

        return [
            'images'    =>  $images,
            'type'      =>  $type,
            'columns'   =>  $columns,
        ];
    }
}
