<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\WebHooks;

use BetaKiller\Action\WebHookExecuteAction;
use BetaKiller\Factory\WebHookFactory;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\WebHookLogInterface;
use BetaKiller\Model\WebHookModelInterface;
use BetaKiller\Repository\WebHookLogRepository;
use Psr\Http\Message\ServerRequestInterface;

class InfoItemIFace extends AbstractAdminIFace
{
    /**
     * @var \BetaKiller\Factory\WebHookFactory
     */
    private $factory;

    /**
     * @var \BetaKiller\Repository\WebHookLogRepository
     */
    private $logRepo;

    /**
     * @param \BetaKiller\Factory\WebHookFactory          $factory
     * @param \BetaKiller\Repository\WebHookLogRepository $logRepo
     */
    public function __construct(WebHookFactory $factory, WebHookLogRepository $logRepo)
    {
        $this->factory = $factory;
        $this->logRepo = $logRepo;
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
        $listIFace    = $urlHelper->getUrlElementByCodename(ListItemsIFace::codename());
        $listItemsUrl = $urlHelper->makeUrl($listIFace, null, false);

        //
        $webHook    = $this->factory->createFromModel($model);
        $definition = $webHook->getRequestDefinition();

        $param = $urlHelper->createUrlContainer();
        $param->setEntity($model);
        $executeAction = $urlHelper->getUrlElementByCodename(WebHookExecuteAction::codename());
        $requestAction = $urlHelper->makeUrl($executeAction, $param, false);

        $codeName    = $model->getCodename();
        $serviceName = $model->getServiceName();
        $eventName   = $model->getEventName();

        return [
            'listItemsUrl' => $listItemsUrl,
            'info'         => [
                'code'    => $codeName,
                'service' => $serviceName,
                'event'   => $eventName,
            ],
            'request'      => [
                'action' => $requestAction,
                'method' => $definition->getMethod(),
                'fields' => $definition->getFields(),
            ],
            'logItems'     => $this->getLogItems($model->getCodename()),
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
        $logItems = $this->logRepo->getItems($codeName);

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
