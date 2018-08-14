<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\WebHooks;

use BetaKiller\Factory\WebHookFactory;
use BetaKiller\IFace\Admin\AbstractAdminBase;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\WebHookModelInterface;
use BetaKiller\Helper\IFaceHelper;

class InfoItem extends AbstractAdminBase
{
    const LIST_ITEMS_IFACE_CODENAME = 'Admin_WebHooks_ListItems';

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
     * @param \BetaKiller\Url\UrlElementTreeInterface         $tree
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     * @param \BetaKiller\Factory\WebHookFactory              $webHookFactory
     * @param \BetaKiller\Helper\IFaceHelper                  $ifaceHelper
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        UrlContainerInterface $urlContainer,
        WebHookFactory $webHookFactory,
        IFaceHelper $ifaceHelper
    ) {
        $this->urlContainer   = $urlContainer;
        $this->webHookFactory = $webHookFactory;
        $this->ifaceHelper    = $ifaceHelper;
        $this->tree           = $tree;
    }

    /**
     * Returns data for View
     *
     * @return array
     */
    public function getData(): array
    {
        $urlElement   = $this->tree->getByCodename(self::LIST_ITEMS_IFACE_CODENAME);
        $listItemsUrl = $this->ifaceHelper->makeUrl($urlElement, null, false);

        $model   = $this->urlContainer->getEntity(WebHookModelInterface::URL_CONTAINER_KEY);
        $webHook = $this->webHookFactory->createFromUrlElement($model);

        $param = UrlContainer::create();
        $param->setEntity($model);
        $requestAction = $this->ifaceHelper->makeUrl($model, $param, false);

        $request = $webHook->getRequestDefinition();

        $codeName    = $model->getCodename();
        $serviceName = $model->getServiceName();
        $eventName   = $model->getEventName();
        $info        = [
            'code'    => $codeName,
            'service' => $serviceName,
            'event'   => $eventName,
        ];

        return [
            'listItemsUrl' => $listItemsUrl,
            'info'         => $info,
            'request'      => [
                'action' => $requestAction,
                'method' => $request->getMethod(),
                'fields' => $request->getFields(),
            ],
        ];
    }
}
