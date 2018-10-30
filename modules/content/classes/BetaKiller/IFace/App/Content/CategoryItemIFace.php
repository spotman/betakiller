<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ContentUrlContainerHelper;
use Psr\Http\Message\ServerRequestInterface;

class CategoryItemIFace extends AbstractAppBase
{
    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $category = ContentUrlContainerHelper::getContentCategory($request);

        if (!$category) {
            throw new BadRequestHttpException;
        }

        return [
            'category' => [
                'label' => $category->getLabel(),
            ],
        ];
    }
}
