<?php
declare(strict_types=1);

namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\AvailableUrlsCollector;
use Psr\Http\Message\ServerRequestInterface;

class SitemapIFace extends AbstractIFace
{
    /**
     * @var \BetaKiller\Url\AvailableUrlsCollector
     */
    private $collector;

    public function __construct(AvailableUrlsCollector $collector)
    {
        $this->collector = $collector;
    }

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
        $data      = [];

        foreach ($this->collector->getPublicAvailableUrls($urlHelper) as $item) {
            $data[] = $item->getUrl();
        }

        return [
            'urls' => $data,
        ];
    }
}
