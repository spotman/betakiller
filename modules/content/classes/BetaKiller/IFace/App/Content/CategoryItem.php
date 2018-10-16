<?php
namespace BetaKiller\IFace\App\Content;

use Psr\Http\Message\ServerRequestInterface;

class CategoryItem extends AbstractAppBase
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $category = $this->urlParametersHelper->getContentCategory($request);

        return [
            'category'  =>  [
               'label'  =>  $category->getLabel(),
            ],
        ];
    }
}
