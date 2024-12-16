<?php

namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class AbstractChildrenListingIFace extends AbstractIFace
{
    /**
     * AbstractChildrenListingIFace constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Helper\UrlElementHelper     $elementHelper
     */
    public function __construct(private UrlElementTreeInterface $tree, private UrlElementHelper $elementHelper)
    {
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $params    = ServerRequestHelper::getUrlContainer($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $i18n      = ServerRequestHelper::getI18n($request);

        $data = [];

        $current = $this->elementHelper->getInstanceModel($this);

        foreach ($this->tree->getChildren($current) as $urlElement) {
            // Show only ifaces
            if (!$urlElement instanceof IFaceModelInterface) {
                continue;
            }

            $data[] = [
                'label'    => $this->elementHelper->getLabel($urlElement, $params, $i18n->getLang()),
                'codename' => $urlElement->getCodename(),
                'url'      => $urlHelper->makeUrl($urlElement),
            ];
        }

        return [
            'ifaces' => $data,
        ];
    }
}
