<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Action\Admin\NotificationMessageTestAction;
use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Query\NotificationLogQuery;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Url\Parameter\NotificationMessageCodename;
use BetaKiller\Url\Parameter\Page;
use Psr\Http\Message\ServerRequestInterface;

readonly class MessageItemIFace extends AbstractAdminIFace
{
    use LogsListTrait;

    public function __construct(private NotificationConfigInterface $config, private NotificationLogRepositoryInterface $logRepo)
    {
    }

    /**
     * @inheritDoc
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $user = ServerRequestHelper::getUser($request);

        /** @var NotificationMessageCodename $item */
        $item = ServerRequestHelper::getParameter($request, NotificationMessageCodename::class);

        $messageCodename = $item->getValue();

        $isTestable = !empty($this->config->getMessageClassName($messageCodename));

        $query = (new NotificationLogQuery())
            ->withMessageCodename($messageCodename);

        $searchResult = $this->logRepo->search($query, 1, 30);

        $logsItems = $this->getListData($searchResult, $urlHelper, null, null, null, null);

        $logsParams = $urlHelper->createUrlContainer()->setParameter(Page::create(2));

        return [
            'name'     => $messageCodename,
            'user_id'  => $user->getID(),
            'logs_items' => $logsItems,
            'logs_url' => $searchResult->hasNextPage() ? $urlHelper->makeCodenameUrl(LogIndexIFace::codename(), $logsParams) : null,
            'test_url' => $isTestable ? $urlHelper->makeCodenameUrl(NotificationMessageTestAction::codename()) : null,
        ];
    }
}
