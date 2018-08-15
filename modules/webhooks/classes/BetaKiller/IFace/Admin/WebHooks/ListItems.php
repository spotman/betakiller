<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\WebHooks;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\Admin\AbstractAdminBase;
use BetaKiller\Repository\WebHookRepository;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\UrlElementTreeInterface;

class ListItems extends AbstractAdminBase
{
    private const INFO_ITEM_IFACE_CODENAME = 'Admin_WebHooks_InfoItem';

    /**
     * @var \BetaKiller\Repository\WebHookRepository
     */
    private $webHookRepository;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * Index constructor.
     *
     * @param \BetaKiller\Repository\WebHookRepository $webHookRepository
     * @param \BetaKiller\Url\UrlElementTreeInterface  $tree
     * @param \BetaKiller\Helper\IFaceHelper           $ifaceHelper
     */
    public function __construct(
        WebHookRepository $webHookRepository,
        UrlElementTreeInterface $tree,
        IFaceHelper $ifaceHelper
    ) {
        $this->webHookRepository = $webHookRepository;
        $this->ifaceHelper       = $ifaceHelper;
        $this->tree              = $tree;
    }

    /**
     * Returns data for View
     *
     * @return array
     */
    public function getData(): array
    {
        $items  = [];
        $models = $this->webHookRepository->getAll();
        foreach ($models as $model) {
            $urlElement = $this->tree->getByCodename(self::INFO_ITEM_IFACE_CODENAME);
            $param      = UrlContainer::create();
            $param->setEntity($model);
            $url = $this->ifaceHelper->makeUrl($urlElement, $param, false);

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
