<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\TextHelper;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MarkNotificationAsReadAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Repository\NotificationLogRepositoryInterface
     */
    private NotificationLogRepositoryInterface $logRepo;

    /**
     * MarkNotificationAsReadAction constructor.
     *
     * @param \BetaKiller\Repository\NotificationLogRepositoryInterface $logRepo
     */
    public function __construct(NotificationLogRepositoryInterface $logRepo)
    {
        $this->logRepo = $logRepo;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $ref = ServerRequestHelper::getHttpReferrer($request);

        // Prevent "read" marker to be set during logs check in admin zone
        if ($ref && TextHelper::contains($ref, '/admin')) {
            return $this->makePixel();
        }

        /** @var NotificationLogInterface $logRecord */
        $logRecord = ServerRequestHelper::getEntity($request, NotificationLogInterface::class);

        if (!$logRecord->isRead()) {
            $logRecord->markAsRead();
            $this->logRepo->save($logRecord);
        }

        return $this->makePixel();
    }

    /**
     * https://stackoverflow.com/a/3203394
     */
    private function makePixel(): ResponseInterface
    {
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');

        return ResponseHelper::fileContent($pixel, 'image/png');
    }
}
