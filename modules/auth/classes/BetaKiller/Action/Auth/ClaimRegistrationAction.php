<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Action\GetRequestActionInterface;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\RegistrationClaimThanksIFace;
use BetaKiller\Model\UserStatus;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Repository\UserStatusRepositoryInterface;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class ClaimRegistrationAction extends AbstractAction implements GetRequestActionInterface
{
    public const NOTIFICATION = 'support/claim-registration';

    private const ARG_HASH = 'h';

    /**
     * @var \BetaKiller\Repository\NotificationLogRepositoryInterface
     */
    private $logRepo;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $facade;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Repository\UserStatusRepositoryInterface
     */
    private $statusRepo;

    /**
     * ClaimRegistrationAction constructor.
     *
     * @param \BetaKiller\Repository\NotificationLogRepositoryInterface $logRepo
     * @param \BetaKiller\Helper\NotificationHelper                     $notification
     * @param \BetaKiller\Repository\LanguageRepositoryInterface        $langRepo
     * @param \BetaKiller\Repository\UserStatusRepositoryInterface      $statusRepo
     * @param \BetaKiller\Repository\UserRepository                     $userRepo
     */
    public function __construct(
        NotificationLogRepositoryInterface $logRepo,
        NotificationHelper $notification,
        LanguageRepositoryInterface $langRepo,
        UserStatusRepositoryInterface $statusRepo,
        UserRepository $userRepo
    ) {
        $this->logRepo    = $logRepo;
        $this->facade     = $notification;
        $this->langRepo   = $langRepo;
        $this->userRepo   = $userRepo;
        $this->statusRepo = $statusRepo;
    }

    public function defineGetArguments(DefinitionBuilderInterface $builder): void
    {
        $builder->string(self::ARG_HASH);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $get = ActionRequestHelper::getArguments($request);

        $hash = $get->getString(self::ARG_HASH);

        $log = $this->logRepo->getByHash($hash);

        $user = $log->getTargetUser();

        if (!$user) {
            // Registration claim is always occurred after sending email to user
            throw new BadRequestHttpException('Registration claim workflow hack');
        }

        if (!$user->getStatus()->isClaimed()) {
            // Mark user as "claimed" to prevent future communication
            $status = $this->statusRepo->getByCodename(UserStatus::STATUS_CLAIMED);
            $user->setStatus($status);
            $this->userRepo->save($user);

            $this->facade->groupMessage(self::NOTIFICATION, [
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
