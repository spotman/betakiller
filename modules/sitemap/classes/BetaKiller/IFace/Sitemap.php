<?php
declare(strict_types=1);

namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ServerRequestInterface;

class Sitemap extends AbstractIFace
{
    /**
     * @Inject
     * @var \BetaKiller\Url\AvailableUrlsCollector
     */
    private $urlsCollector;

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $data = [];

        foreach ($this->urlsCollector->getPublicAvailableUrls($urlHelper) as $item) {
            $data[] = $item->getUrl();
        }

        return [
            'urls' => $data,
        ];
    }
}
