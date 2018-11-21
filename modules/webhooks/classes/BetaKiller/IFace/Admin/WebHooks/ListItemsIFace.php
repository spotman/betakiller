<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\WebHooks;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Repository\WebHookRepository;
use BetaKiller\Url\UrlElementTreeInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListItemsIFace extends AbstractAdminIFace
{
    /**
     * @var \BetaKiller\Repository\WebHookRepository
     */
    private $webHookRepository;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * Index constructor.
     *
     * @param \BetaKiller\Repository\WebHookRepository $webHookRepository
     * @param \BetaKiller\Url\UrlElementTreeInterface  $tree
     */
    public function __construct(
        WebHookRepository $webHookRepository,
        UrlElementTreeInterface $tree
    ) {
        $this->webHookRepository = $webHookRepository;
        $this->tree              = $tree;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlElementException
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
