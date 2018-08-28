<?php

use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupRole;
use BetaKiller\Model\NotificationGroupUserOff;
use BetaKiller\Url\UrlDispatcher;

/**
 * Class Controller_IFace
 */
class Controller_IFace extends Controller
{
    /**
     * @Inject
     * @var \BetaKiller\Factory\UrlElementProcessorFactory
     */
    private $processorFactory;

    /**
     * @Inject
     * @var UrlDispatcher
     */
    private $urlDispatcher;

    /**
     * Manager of URL element parameters
     *
     * @Inject
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * @Inject
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private $notificationGroupRepository;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepository;

    /**
     * @deprecated
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\SeeOtherHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Spotman\Acl\Exception
     */
    public function action_render(): void
    {
        $this->actionRender();
    }

    /**
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\SeeOtherHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Spotman\Acl\Exception
     */
    public function actionRender(): void
    {
//        echo '<pre>';

//        $notificationGroupModel = (new NotificationGroup())
//            ->setCodename('test2')
//            ->setDescription('It is test');
//        $items = $this->notificationGroupRepository->getItems();
//        var_dump('<pre>');
//        foreach ($items as $item) {
//            var_dump($item->getAll());
//        }
        $item = $this->notificationGroupRepository->getGroupsOff();
        var_dump('-------');
        var_dump($item);
//        var_dump($items->getAll());
///
///
//        $d = (new NotificationGroup)->getGroupsOff();
//        var_dump($d);
//        exit;
//        foreach ($d as $dd) {
//            var_dump('----------------------------------------');
////            var_dump($dd->getGroupId());
////            var_dump($dd->getGroupCodename());
////            var_dump($dd->getCodename());
//        }

        exit;


//        $d = $this->user->getNotificationGroups();
////        var_dump($d);
////        exit;
//        foreach ($d as $dd) {
//            var_dump('----------------------------------------');
//            var_dump($dd->getGroupId());
////            var_dump($dd->getGroupCodename());
////            var_dump($dd->getCodename());
//        }

//        $d = $this->roleRepository->getLoginRole();
////        var_dump(get_class($d));
////        var_dump($d->getName());
////        exit;
//        $d = $d->getNotificationGroups();
//        foreach ($d as $dd) {
//            var_dump('----------------------------------------');
//            var_dump($dd->getGroupCodename());
////            var_dump($dd->getName());
////            var_dump($dd->getCodename());
//        }
        exit;

        $this->urlContainer->setQueryParts($this->request->query());

        $urlElement   = $this->urlDispatcher->process(
            $this->request->url(),
            $this->request->client_ip(),
            $this->request->referrer()
        );
        $urlProcessor = $this->processorFactory->createFromUrlElement($urlElement);
        $urlProcessor->process($urlElement, $this->urlContainer, $this->response, $this->request);
    }
}
