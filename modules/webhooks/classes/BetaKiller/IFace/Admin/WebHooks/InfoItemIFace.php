<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\WebHooks;

use BetaKiller\Factory\WebHookFactory;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminBase;
use BetaKiller\Model\WebHookLogInterface;
use BetaKiller\Repository\WebHookLogRepository;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\WebHookModelInterface;
use Psr\Http\Message\ServerRequestInterface;

class InfoItemIFace extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Factory\WebHookFactory
     */
    private $webHookFactory;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Repository\WebHookLogRepository
     */
    private $webHookLogRepository;

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface     $tree
     * @param \BetaKiller\Factory\WebHookFactory          $webHookFactory
     * @param \BetaKiller\Repository\WebHookLogRepository $webHookLogRepository
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        WebHookFactory $webHookFactory,
        WebHookLogRepository $webHookLogRepository
    ) {
        $this->webHookFactory       = $webHookFactory;
        $this->tree                 = $tree;
        $this->webHookLogRepository = $webHookLogRepository;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        /** @var WebHookModelInterface $model */
        $model = ServerRequestHelper::getEntity($request, WebHookModelInterface::class);

        //
        $urlElement   = $this->tree->getByCodename(ListItemsIFace::codename());
        $listItemsUrl = $urlHelper->makeUrl($urlElement, null, false);

        //
        $webHook    = $this->webHookFactory->createFromUrlElement($model);
        $definition = $webHook->getRequestDefinition();

        $param = UrlContainer::create();
        $param->setEntity($model);
        $requestAction = $urlHelper->makeUrl($model, $param, false);

        $codeName    = $model->getCodename();
        $serviceName = $model->getServiceName();
        $eventName   = $model->getEventName();
        $info        = [
            'code'    => $codeName,
            'service' => $serviceName,
            'event'   => $eventName,
        ];

        //
        $logItems = $this->getLogItems($model->getCodename());

        //
        return [
            'listItemsUrl' => $listItemsUrl,
            'info'         => $info,
            'request'      => [
                'action' => $requestAction,
                'method' => $definition->getMethod(),
                'fields' => $definition->getFields(),
            ],
            'logItems'     => $logItems,
        ];
    }

    /**
     * @param string $codeName
     *
     * @return array[
     *  [
     *      string id,
     *      string codeName,
     *      \DateTimeImmutable dateCreated,
     *      int status,
     *      string message,
     *      array requestData
     *  ],
     *  ..
     * ]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getLogItems(string $codeName): array
    {
        $logItems = $this->webHookLogRepository->getItems($codeName);

        return array_map(function (WebHookLogInterface $model) {
            return [
                'id'          => $model->getID(),
                'codeName'    => $model->getCodename(),
                'dateCreated' => $model->getCreatedAt(),
                'status'      => (int)$model->isStatusSucceeded(),
                'message'     => $model->getMessage(),
                'requestData' => $model->getRequestData()->get(),
            ];
        }, $logItems);
    }
}
