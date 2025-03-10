<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\RegistrationClaimThanksIFace;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Url\Zone;
use BetaKiller\Workflow\UserWorkflow;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class ClaimRegistrationAction extends AbstractAction
{
    public const NOTIFICATION = 'email/support/claim-registration';

    /**
     * ClaimRegistrationAction constructor.
     *
     * @param \BetaKiller\Helper\NotificationHelper              $notification
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     * @param \BetaKiller\Repository\UserRepositoryInterface     $userRepo
     * @param \BetaKiller\Workflow\UserWorkflow                  $userWorkflow
     */
    public function __construct(
        private NotificationHelper $notification,
        private LanguageRepositoryInterface $langRepo,
        private UserRepositoryInterface $userRepo,
        private UserWorkflow $userWorkflow
    ) {
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        /** @var NotificationLogInterface $log */
        $log = ServerRequestHelper::getEntity($request, NotificationLogInterface::class);

        if (!$log) {
            throw new BadRequestHttpException('Missing notification log record');
        }

        $userId = $log->getTargetUserId();

        if (!$userId) {
            // Registration claim is always occurred after sending email to user
            throw new BadRequestHttpException('Registration claim workflow hack');
        }

        $user = $this->userRepo->getById($userId);

        // Prevent errors on multiple calls from different emails
        if (!$user->isRegistrationClaimed()) {
            $this->userWorkflow->notRegisteredClaim($user);

            $this->notification->broadcastMessage(self::NOTIFICATION, [
                'email'             => $log->getTargetIdentity(),
                'ip'                => ServerRequestHelper::getIpAddress($request),
                'notification_url'  => $urlHelper->getReadEntityUrl($log, Zone::admin()),
                'notification_hash' => $log->getHash(),
            ]);

            // User went from an email link, so confirm email too
            $this->userWorkflow->confirmEmail($user);
        }

        $lang = $this->langRepo->getByIsoCode($log->getLanguageIsoCode());

        $params = $urlHelper->createUrlContainer()->setEntity($lang);

        return ResponseHelper::redirect(
            $urlHelper->makeCodenameUrl(RegistrationClaimThanksIFace::codename(), $params)
        );
    }
}
