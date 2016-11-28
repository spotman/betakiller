<?php

use BetaKiller\IFace\Widget;

class Widget_CustomTag_Attachment extends Widget
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

        $attach_id = (int) $context['id'];

        if (!$attach_id)
            throw new Widget\Exception('No attachment ID provided');

        $model = $this->model_factory_content_attachment_element()->get_by_id($attach_id);

        $title  = HTML::chars(Arr::get($context, 'title'));
        $class  = Arr::get($context, 'class');

        return [
            'attachment'    =>  [
                'url'       =>  $model->get_original_url(),
                'title'     =>  $title ?: __('custom_tag.attachment.title'),
                'alt'       =>  __('custom_tag.attachment.alt'),
                'class'     =>  $class,
            ],
        ];
    }
}
