<?php
declare(strict_types=1);

namespace BetaKiller\IFace;

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
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        $data = [];

        foreach ($this->urlsCollector->getPublicAvailableUrls() as $item) {
            $data[] = $item->getUrl();
        }

        return [
            'urls' => $data,
        ];
    }
}
