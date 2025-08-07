<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Acl\Resource\NotificationLogResource;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Query\NotificationLogQuery;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Url\Parameter\Page;
use BetaKiller\Url\Zone;
use Psr\Http\Message\ServerRequestInterface;

final readonly class LogIndexIFace extends AbstractAdminIFace
{
    public const ARG_MESSAGE   = 'message';
    public const ARG_TARGET    = 'target';
    public const ARG_STATUS    = 'status';
    public const ARG_TRANSPORT = 'transport';
    public const ARG_PAGE      = 'page';

    /**
     * LogIndexIFace constructor.
     *
     * @param \BetaKiller\Repository\NotificationLogRepositoryInterface $logRepo
     */
    public function __construct(private NotificationLogRepositoryInterface $logRepo)
    {
    }

    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $urlParams = ServerRequestHelper::getUrlContainer($request);

        $filterMessageName   = $urlParams->getQueryPart(self::ARG_MESSAGE);
        $filterTargetId      = $urlParams->getQueryPart(self::ARG_TARGET);
        $filterStatusName    = $urlParams->getQueryPart(self::ARG_STATUS);
        $filterTransportName = $urlParams->getQueryPart(self::ARG_TRANSPORT);

        $pageParam   = Page::fromRequest($request, 1);
        $currentPage = $pageParam->getValue();

        $query = new NotificationLogQuery();

        if ($filterMessageName) {
            $query->withMessageCodename($filterMessageName);
        }

        if ($filterTargetId) {
            $query->forTargetIdentity($filterTargetId);
        }

        if ($filterStatusName) {
            $query->withStatus($filterStatusName);
        }

        if ($filterTransportName) {
            $query->throughTransport($filterTransportName);
        }

        $searchResult = $this->logRepo->search($query, $currentPage, 30);

        $data = [];

        foreach ($searchResult as $item) {
            $data[] = $this->getItemData($item, $urlHelper, $filterTargetId, $filterMessageName, $filterStatusName, $filterTransportName);
        }

        $nextPageUrl = $searchResult->hasNextPage()
            ? $this->makeFilterUrl($filterTargetId, $filterMessageName, $filterStatusName, $filterTransportName, $currentPage + 1)
            : null;

        $prevPageUrl = $currentPage > 1
            ? $this->makeFilterUrl($filterTargetId, $filterMessageName, $filterStatusName, $filterTransportName, $currentPage - 1)
            : null;

        return [
            'items'         => $data,
            'next_page_url' => $nextPageUrl,
            'prev_page_url' => $prevPageUrl,

            'filters' => [
                'defined' => $filterTargetId || $filterMessageName || $filterStatusName || $filterTransportName,
                'target'  => [
                    'identity'  => $filterTargetId,
                    'clear_url' => $this->makeFilterUrl(null, $filterMessageName, $filterStatusName, $filterTransportName),
                ],
                'message' => [
                    'name'      => $filterMessageName,
                    'clear_url' => $this->makeFilterUrl($filterTargetId, null, $filterStatusName, $filterTransportName),
                ],

                'status'    => [
                    'name'      => $filterStatusName,
                    'clear_url' => $this->makeFilterUrl($filterTargetId, $filterMessageName, null, $filterTransportName),

                ],
                'transport' => [
                    'name'      => $filterTransportName,
                    'clear_url' => $this->makeFilterUrl($filterTargetId, $filterMessageName, $filterStatusName, null),
                ],
            ],
        ];
    }

    private function makeFilterUrl(
        ?string $targetId,
        ?string $messageName,
        ?string $statusName,
        ?string $transportName,
        ?int $page = null
    ): string {
        return '?'.http_build_query(
                array_filter([
                    self::ARG_TARGET    => $targetId,
                    self::ARG_MESSAGE   => $messageName,
                    self::ARG_STATUS    => $statusName,
                    self::ARG_TRANSPORT => $transportName,
                    self::ARG_PAGE      => $page,
                ])
            );
    }

    private function getItemData(
        NotificationLogInterface $item,
        UrlHelperInterface $urlHelper,
        ?string $filterTargetId,
        ?string $filterMessageName,
        ?string $filterStatusName,
        ?string $filterTransportName
    ): array {
        $itemTargetId      = $item->getTargetIdentity();
        $itemStatusName    = $item->getStatus();
        $itemMessageName   = $item->getMessageName();
        $itemTransportName = $item->getTransportName();

        return [
            'processed_at' => $item->getProcessedAt(),
            'name'         => $itemMessageName,
            'transport'    => $itemTransportName,
            'target'       => $item->getTargetIdentity(),
            'is_pending'   => $item->isPending(),
            'is_succeeded' => $item->isSucceeded(),
            'is_failed'    => $item->isFailed(),
            'status'       => $itemStatusName,
            'is_read'      => $item->isRead(),
            'body_url'     => $urlHelper->getReadEntityUrl($item, Zone::admin()),
            'retry_url'    => $item->isRetryAvailable()
                ? $urlHelper->getEntityUrl($item, NotificationLogResource::ACTION_RETRY, Zone::admin())
                : null,

            'user_url'      => $this->makeFilterUrl($itemTargetId, $filterMessageName, $filterStatusName, $filterTransportName),
            'message_url'   => $this->makeFilterUrl($filterTargetId, $itemMessageName, $filterStatusName, $filterTransportName),
            'status_url'    => $this->makeFilterUrl($filterTargetId, $filterMessageName, $itemStatusName, $filterTransportName),
            'transport_url' => $this->makeFilterUrl($filterTargetId, $filterMessageName, $filterStatusName, $itemTransportName),
        ];
    }
}
