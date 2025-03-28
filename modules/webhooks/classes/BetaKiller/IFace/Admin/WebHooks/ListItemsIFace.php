<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\WebHooks;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Repository\WebHookRepositoryInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class ListItemsIFace extends AbstractAdminIFace
{
    /**
     * Index constructor.
     *
     * @param \BetaKiller\Repository\WebHookRepositoryInterface $webHookRepository
     * @param \BetaKiller\Url\UrlElementTreeInterface           $tree
     */
    public function __construct(
        private WebHookRepositoryInterface $webHookRepository,
        private UrlElementTreeInterface $tree
    ) {
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $items = [];

        foreach ($this->webHookRepository->getAll() as $model) {
            $urlElement = $this->tree->getByCodename(InfoItemIFace::codename());

            $param = $urlHelper->createUrlContainer()->setEntity($model);
            $url   = $urlHelper->makeUrl($urlElement, $param, false);

            $codeName    = $model->getCodename();
            $serviceName = $model->getServiceName();
            $eventName   = $model->getEventName();
            $items[]     = [
                'url'  => $url,
                'info' => [
                    'code'    => $codeName,
                    'service' => $serviceName,
                    'event'   => $eventName,
                ],
            ];
        }

        return [
            'items' => $items,
        ];
    }
}
