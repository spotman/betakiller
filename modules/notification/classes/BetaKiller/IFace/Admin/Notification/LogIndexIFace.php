<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Repository\NotificationLogRepository;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Url\Parameter\PaginationUrlParameter;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogIndexIFace extends AbstractAdminIFace
{
    public const ARG_MESSAGE = 'message';
    public const ARG_USER    = 'user';

    /**
     * @var \BetaKiller\Repository\NotificationLogRepository
     */
    private $logRepo;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * LogIndexIFace constructor.
     *
     * @param \BetaKiller\Repository\NotificationLogRepository $logRepo
     * @param \BetaKiller\Repository\UserRepositoryInterface   $userRepo
     */
    public function __construct(NotificationLogRepository $logRepo, UserRepositoryInterface $userRepo)
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

    private function getItemData(NotificationLogInterface $item, UrlHelper $urlHelper): array
    {
        return [
            'processed_at' => $item->getProcessedAt(),
            'name'         => $item->getMessageName(),
            'transport'    => $item->getTransportName(),
            'target'       => $item->getTargetString(),
            'is_succeeded' => $item->isSucceeded(),
            'body_url'     => $urlHelper->getReadEntityUrl($item, ZoneInterface::ADMIN),
        ];
    }
}
