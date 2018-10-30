<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlContainerHelper;
use Psr\Http\Message\ServerRequestInterface;

class CommentItemIFace extends AbstractAdminBase
{
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
        $model = ContentUrlContainerHelper::getContentComment($request);

        return [
            'id'      => $model->getID(),
            'message' => $model->getMessage(),
        ];
    }
}
