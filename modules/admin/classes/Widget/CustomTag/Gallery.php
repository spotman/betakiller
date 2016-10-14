<?php

use BetaKiller\IFace\Widget;

class Widget_CustomTag_Gallery extends Widget
{
    use BetaKiller\Helper\Admin;

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

        $images = [];

        foreach ($image_ids as $id)
        {
            $model = $this->model_factory_admin_image_file($id);

            $images[] = $model->get_img_tag_arguments_with_srcset();
        }

        return [
            'images'    =>  $images,
            'type'      =>  $type,
        ];
    }
}
