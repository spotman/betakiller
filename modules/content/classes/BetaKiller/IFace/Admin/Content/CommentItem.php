<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentItem extends AbstractAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
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
            'id'        =>  $model->getID(),
            'message'   =>  $model->getMessage(),
        ];
    }
}
