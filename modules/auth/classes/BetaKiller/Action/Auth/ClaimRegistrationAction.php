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
use BetaKiller\Url\ZoneInterface;
use BetaKiller\Workflow\UserWorkflow;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClaimRegistrationAction extends AbstractAction
{
    public const NOTIFICATION = 'email/support/claim-registration';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $facade;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Workflow\UserWorkflow
     */
    private $userWorkflow;

    /**
     * ClaimRegistrationAction constructor.
     *
     * @param \BetaKiller\Helper\NotificationHelper              $notification
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     * @param \BetaKiller\Repository\UserRepositoryInterface     $userRepo
     * @param \BetaKiller\Workflow\UserWorkflow                  $userWorkflow
     */
    public function __construct(
        NotificationHelper $notification,
        LanguageRepositoryInterface $langRepo,
        UserRepositoryInterface $userRepo,
        UserWorkflow $userWorkflow
    ) {
        $this->facade       = $notification;
        $this->langRepo     = $langRepo;
        $this->userRepo     = $userRepo;
        $this->userWorkflow = $userWorkflow;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $log = ServerRequestHelper::getEntity($request, NotificationLogInterface::class);

        $userId = $log->getTargetUserId();

        if (!$userId) {
            // Registration claim is always occurred after sending email to user
            throw new BadRequestHttpException('Registration claim workflow hack');
        }

        $user = $this->userRepo->getById($userId);

        // Prevent errors on multiple calls from different emails
        if (!$user->isRegistrationClaimed()) {
            $this->userWorkflow->notRegisteredClaim($user);

            $this->facade->broadcastMessage(self::NOTIFICATION, [
                'email'             => $log->getTargetString(),
                'ip'                => ServerRequestHelper::getIpAddress($request),
                'notification_url'  => $urlHelper->getReadEntityUrl($log, ZoneInterface::ADMIN),
                'notification_hash' => $log->getHash(),
            ]);
        }

        $lang = $this->langRepo->getByIsoCode($log->getLanguageIsoCode());

        $thanksElement = $urlHelper->getUrlElementByCodename(RegistrationClaimThanksIFace::codename());
        $params        = $urlHelper->createUrlContainer()->setEntity($lang);

        return ResponseHelper::redirect(
            $urlHelper->makeUrl($thanksElement, $params)
        );
    }
}
