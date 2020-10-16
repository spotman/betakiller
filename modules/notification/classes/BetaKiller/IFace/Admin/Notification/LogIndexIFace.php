<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Url\Parameter\PaginationUrlParameter;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LogIndexIFace extends AbstractAdminIFace
{
    public const ARG_MESSAGE = 'message';
    public const ARG_USER    = 'user';

    /**
     * @var \BetaKiller\Repository\NotificationLogRepositoryInterface
     */
    private NotificationLogRepositoryInterface $logRepo;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepo;

    /**
     * LogIndexIFace constructor.
     *
     * @param \BetaKiller\Repository\NotificationLogRepositoryInterface $logRepo
     * @param \BetaKiller\Repository\UserRepositoryInterface            $userRepo
     */
    public function __construct(NotificationLogRepositoryInterface $logRepo, UserRepositoryInterface $userRepo)
    {
        $this->logRepo  = $logRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $urlParams = ServerRequestHelper::getUrlContainer($request);

        $itemsPerPage = 50;

        $messageCodename = $urlParams->getQueryPart(self::ARG_MESSAGE);
        $userID          = $urlParams->getQueryPart(self::ARG_USER);

        /** @var PaginationUrlParameter $pageParam */
        $pageParam   = ServerRequestHelper::getParameter($request, PaginationUrlParameter::class);
        $currentPage = $pageParam ? $pageParam->getValue() : 1;

        if ($messageCodename) {
            $items = $this->logRepo->getMessageList($messageCodename, $currentPage, $itemsPerPage);
        } elseif ($userID) {
            $user  = $this->userRepo->getById($userID);
            $items = $this->logRepo->getUserList($user, $currentPage, $itemsPerPage);
        } else {
            $items = $this->logRepo->getList($currentPage, $itemsPerPage);
        }

        $data = [];

        foreach ($items as $item) {
            $data[] = $this->getItemData($item, $urlHelper);
        }

        return [
            'items' => $data,
        ];
    }

    private function getItemData(NotificationLogInterface $item, UrlHelperInterface $urlHelper): array
    {
        return [
            'processed_at' => $item->getProcessedAt(),
            'name'         => $item->getMessageName(),
            'transport'    => $item->getTransportName(),
            'target'       => $item->getTargetString(),
            'is_succeeded' => $item->isSucceeded(),
            'is_read'      => $item->isRead(),
            'body_url'     => $urlHelper->getReadEntityUrl($item, ZoneInterface::ADMIN),
        ];
    }
}
