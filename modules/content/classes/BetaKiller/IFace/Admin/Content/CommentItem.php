<?php
namespace BetaKiller\IFace\Admin\Content;

use Psr\Http\Message\ServerRequestInterface;

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
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \Kohana_Exception
     */
    public function getData(ServerRequestInterface $request): array
    {
        $model = $this->urlParametersHelper->getContentComment($request);

        return [
            'id'        =>  $model->getID(),
            'message'   =>  $model->getMessage(),
        ];
    }
}
