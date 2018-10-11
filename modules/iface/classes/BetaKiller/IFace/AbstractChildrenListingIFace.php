<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractChildrenListingIFace extends AbstractIFace
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * AbstractChildrenListingIFace constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     */
    public function __construct(UrlElementTreeInterface $tree)
    {
        $this->tree = $tree;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper     = ServerRequestHelper::getUrlHelper($request);
        $elementHelper = ServerRequestHelper::getUrlElementHelper($request);

        $data = [];

        foreach ($this->tree->getChildren($this->getModel()) as $urlElement) {
            // Show only ifaces
            if (!$urlElement instanceof IFaceModelInterface) {
                continue;
            }

            $data[] = [
                'label'    => $elementHelper->getLabel($urlElement),
                'codename' => $urlElement->getCodename(),
                'url'      => $urlHelper->makeUrl($urlElement),
            ];
        }

        return [
            'ifaces' => $data,
        ];
    }
}
