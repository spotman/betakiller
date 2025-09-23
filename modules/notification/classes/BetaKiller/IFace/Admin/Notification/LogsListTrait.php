<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Acl\Resource\NotificationLogResource;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Search\SearchResultsInterface;
use BetaKiller\Url\Zone;

trait LogsListTrait
{
    private function getListData(
        SearchResultsInterface $searchResult,
        UrlHelperInterface $urlHelper,
        ?string $filterTargetId,
        ?string $filterMessageName,
        ?string $filterStatusName,
        ?string $filterTransportName
    ): array {
        $data = [];

        foreach ($searchResult as $item) {
            $data[] = $this->getItemData($item, $urlHelper, $filterTargetId, $filterMessageName, $filterStatusName, $filterTransportName);
        }

        return $data;
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
            'is_rejected'  => $item->isRejected(),
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

    private function makeFilterUrl(
        ?string $targetId,
        ?string $messageName,
        ?string $statusName,
        ?string $transportName,
        ?int $page = null
    ): string {
        return '/admin/notifications/logs?'.http_build_query(
                array_filter([
                    LogIndexIFace::ARG_TARGET    => $targetId,
                    LogIndexIFace::ARG_MESSAGE   => $messageName,
                    LogIndexIFace::ARG_STATUS    => $statusName,
                    LogIndexIFace::ARG_TRANSPORT => $transportName,
                    LogIndexIFace::ARG_PAGE      => $page,
                ])
            );
    }
}
