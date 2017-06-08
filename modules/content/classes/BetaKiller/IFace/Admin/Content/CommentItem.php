<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentItem extends AbstractAdminBase
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
    public function getData(): array
    {
        $model = $this->urlParametersHelper->getContentComment();

        return [
            'id'        =>  $model->get_id(),
            'message'   =>  $model->get_message(),
        ];
    }
}
