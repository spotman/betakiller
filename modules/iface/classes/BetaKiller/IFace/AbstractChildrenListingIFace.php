<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
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
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $elementHelper;

    /**
     * AbstractChildrenListingIFace constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Helper\UrlElementHelper     $elementHelper
     */
    public function __construct(UrlElementTreeInterface $tree, UrlElementHelper $elementHelper)
    {
        $this->tree          = $tree;
        $this->elementHelper = $elementHelper;
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
        $params    = ServerRequestHelper::getUrlContainer($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $i18n      = ServerRequestHelper::getI18n($request);

        $data = [];

        foreach ($this->tree->getChildren($this->getModel()) as $urlElement) {
            // Show only ifaces
            if (!$urlElement instanceof IFaceModelInterface) {
                continue;
            }

            $data[] = [
                'label'    => $this->elementHelper->getLabel($urlElement, $params, $i18n),
                'codename' => $urlElement->getCodename(),
                'url'      => $urlHelper->makeUrl($urlElement),
            ];
        }

        return [
            'ifaces' => $data,
        ];
    }
}
