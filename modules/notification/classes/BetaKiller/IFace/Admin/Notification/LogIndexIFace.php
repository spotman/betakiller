<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Query\NotificationLogQuery;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Url\Parameter\PaginationUrlParameter;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LogIndexIFace extends AbstractAdminIFace
{
    public const ARG_MESSAGE   = 'message';
    public const ARG_USER      = 'user';
    public const ARG_STATUS    = 'status';
    public const ARG_TRANSPORT = 'transport';

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

        $itemsPerPage = 100;

        $messageCodename = $urlParams->getQueryPart(self::ARG_MESSAGE);
        $userId          = $urlParams->getQueryPart(self::ARG_USER);
        $status          = $urlParams->getQueryPart(self::ARG_STATUS);
        $transport       = $urlParams->getQueryPart(self::ARG_TRANSPORT);

        /** @var PaginationUrlParameter $pageParam */
        $pageParam   = ServerRequestHelper::getParameter($request, PaginationUrlParameter::class);
        $currentPage = $pageParam ? $pageParam->getValue() : 1;

        $user = $userId ? $this->userRepo->getById($userId) : null;

        $query = new NotificationLogQuery;

        if ($messageCodename) {
            $query->withMessageCodename($messageCodename);
        }

        if ($user) {
            $query->forUser($user);
        }

        if ($status) {
            $query->withStatus($status);
        }

        if ($transport) {
            $query->throughTransport($transport);
        }

        $items = $this->logRepo->getList($query, $currentPage, $itemsPerPage);

        $data = [];

        foreach ($items as $item) {
            $data[] = $this->getItemData($item, $urlHelper);
        }

        return [
            'items' => $data,

            'filters' => [
                'user'      => [
                    'id'    => $user ? $user->getID() : null,
                    'name'  => $user ? $user->getFullName() : null,
                    'email' => $user ? $user->getEmail() : null,
                ],
                'message'   => $messageCodename,
                'status'    => $status,
                'transport' => $transport,
            ],
        ];
    }

    private function getItemData(NotificationLogInterface $item, UrlHelperInterface $urlHelper): array
    {
        return [
            'processed_at' => $item->getProcessedAt(),
            'name'         => $item->getMessageName(),
            'transport'    => $item->getTransportName(),
            'target'       => $item->getTargetString(),
            'user_id'      => $item->getTargetUserId(),
            'is_succeeded' => $item->isSucceeded(),
            'status'       => $item->getStatus(),
            'is_read'      => $item->isRead(),
            'body_url'     => $urlHelper->getReadEntityUrl($item, ZoneInterface::ADMIN),
        ];
    }
}
