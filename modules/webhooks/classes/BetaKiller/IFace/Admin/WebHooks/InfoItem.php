<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\WebHooks;

use BetaKiller\Factory\WebHookFactory;
use BetaKiller\IFace\Admin\AbstractAdminBase;
use BetaKiller\Repository\WebHookLogRepository;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\WebHookModelInterface;
use BetaKiller\Helper\IFaceHelper;

class InfoItem extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * @var \BetaKiller\Factory\WebHookFactory
     */
    private $webHookFactory;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Repository\WebHookLogRepository
     */
    private $webHookLogRepository;

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface         $tree
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     * @param \BetaKiller\Factory\WebHookFactory              $webHookFactory
     * @param \BetaKiller\Helper\IFaceHelper                  $ifaceHelper
     * @param \BetaKiller\Repository\WebHookLogRepository     $webHookLogRepository
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        UrlContainerInterface $urlContainer,
        WebHookFactory $webHookFactory,
        IFaceHelper $ifaceHelper,
        WebHookLogRepository $webHookLogRepository
    ) {
        $this->urlContainer         = $urlContainer;
        $this->webHookFactory       = $webHookFactory;
        $this->ifaceHelper          = $ifaceHelper;
        $this->tree                 = $tree;
        $this->webHookLogRepository = $webHookLogRepository;
    }

    /**
     * Returns data for View
     *
     * @return array
     */
    public function getData(): array
    {
        $model = $this->getWebHookModel();

        //
        $urlElement   = $this->tree->getByCodename(ListItems::codename());
        $listItemsUrl = $this->ifaceHelper->makeUrl($urlElement, null, false);

        //
        $webHook = $this->webHookFactory->createFromUrlElement($model);
        $request = $webHook->getRequestDefinition();

        $param = UrlContainer::create();
        $param->setEntity($model);
        $requestAction = $this->ifaceHelper->makeUrl($model, $param, false);

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
                'method' => $request->getMethod(),
                'fields' => $request->getFields(),
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
    protected function getLogItems(string $codeName): array
    {
        $logItems = $this->webHookLogRepository->getItems($codeName);
        foreach ($logItems as &$logItem) {
            $logItem = [
                'id'          => $logItem->getID(),
                'codeName'    => $logItem->getCodename(),
                'dateCreated' => $logItem->getCreatedAt(),
                'status'      => (int)$logItem->isStatusSucceeded(),
                'message'     => $logItem->getMessage(),
                'requestData' => $logItem->getRequestData()->get(),
            ];
        }
        unset($logItem);

        return $logItems;
    }


    /**
     * @return \BetaKiller\Url\WebHookModelInterface
     */
    protected function getWebHookModel(): WebHookModelInterface
    {
        return $this->urlContainer->getEntityByClassName(WebHookModelInterface::class);
    }
}
