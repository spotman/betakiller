<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlParametersHelper;

class CommentItem extends AdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * Returns data for View
     * Override this method in child classes
     */
    public function getData()
    {
        $model = $this->urlParametersHelper->getContentComment();

        return [
            'id'        =>  $model->get_id(),
            'message'   =>  $model->get_message(),
        ];
    }
}
