<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_CustomTag_Attachment extends AbstractBaseWidget
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

        $attach_id = (int) $context['id'];

        if (!$attach_id) {
            throw new WidgetException('No attachment ID provided');
        }

        $model = $this->model_factory_content_attachment_element()->get_by_id($attach_id);

        $title  = HTML::chars(Arr::get($context, 'title'));
        $class  = Arr::get($context, 'class');

        $i18n_params = [
            ':name' =>  $model->getOriginalName(),
        ];

        return [
            'attachment'    =>  [
                'url'       =>  $model->getOriginalUrl(),
                'title'     =>  $title ?: __('custom_tag.attachment.title', $i18n_params),
                'alt'       =>  __('custom_tag.attachment.alt', $i18n_params),
                'class'     =>  $class,
            ],
        ];
    }
}
