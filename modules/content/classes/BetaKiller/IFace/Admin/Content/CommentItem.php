<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentItem extends AdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     */
    public function getData()
    {
        $model = $this->url_parameter_content_comment();

        return [
            'id'        =>  $model->get_id(),
            'message'   =>  $model->get_message(),
        ];
    }
}
