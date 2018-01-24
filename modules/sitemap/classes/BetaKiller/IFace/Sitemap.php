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
     */
    public function getData(): array
    {
        $data = [];

        foreach ($this->urlsCollector->getPublicAvailableUrls() as $item) {
            $data[] = $item;
        }

        return [
            'urls' => $data,
        ];
    }
}
