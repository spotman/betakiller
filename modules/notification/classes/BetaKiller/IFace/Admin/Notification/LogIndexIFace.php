<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Query\NotificationLogQuery;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Url\Parameter\NotificationMessageCodename;
use BetaKiller\Url\Parameter\Page;
use Psr\Http\Message\ServerRequestInterface;

final readonly class LogIndexIFace extends AbstractAdminIFace
{
    use LogsListTrait;

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

        /** @var NotificationMessageCodename|null $codenameParam */
        $codenameParam = $urlParams->getParameterByClassName(NotificationMessageCodename::class);

        $filterMessageName   = $codenameParam?->getValue();
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

        $nextPageUrl = $searchResult->hasNextPage()
            ? $this->makeFilterUrl($filterTargetId, $filterMessageName, $filterStatusName, $filterTransportName, $currentPage + 1)
            : null;

        $prevPageUrl = $currentPage > 1
            ? $this->makeFilterUrl($filterTargetId, $filterMessageName, $filterStatusName, $filterTransportName, $currentPage - 1)
            : null;

        return [
            'items' => $this->getListData(
                $searchResult,
                $urlHelper,
                $filterTargetId,
                $filterMessageName,
                $filterStatusName,
                $filterTransportName
            ),

            'next_page_url' => $nextPageUrl,
            'prev_page_url' => $prevPageUrl,

            'filters' => [
                'defined' => $filterTargetId || $filterMessageName || $filterStatusName || $filterTransportName,

                'target' => [
                    'identity'  => $filterTargetId,
                    'clear_url' => $this->makeFilterUrl(null, $filterMessageName, $filterStatusName, $filterTransportName),
                ],

                'message' => [
                    'name'      => $filterMessageName,
                    'clear_url' => $this->makeFilterUrl($filterTargetId, null, $filterStatusName, $filterTransportName),
                ],

                'status' => [
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
}
