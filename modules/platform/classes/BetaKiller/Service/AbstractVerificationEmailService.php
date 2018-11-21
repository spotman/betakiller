<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\UserRepository;
use Psr\Http\Message\ServerRequestInterface;
use Worknector\Action\App\Verification\Email\ConfirmAction;

abstract class AbstractVerificationEmailService
{
    /**
     * @var \BetaKiller\Service\TokenService
     */
    private $tokenService;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * VerificationEmailService constructor.
     *
     * @param \BetaKiller\Helper\NotificationHelper $notificationHelper
     * @param \BetaKiller\Service\TokenService      $tokenService
     * @param \BetaKiller\Repository\UserRepository $userRepo
     */
    public function __construct(
        NotificationHelper $notificationHelper,
        TokenService $tokenService,
        UserRepository $userRepo
    ) {
        $this->tokenService = $tokenService;
        $this->notification = $notificationHelper;
        $this->userRepo     = $userRepo;
    }

    /**
     * @return string
     */
    abstract protected function getTokenPeriod(): string;

    /**
     * @return string
     */
    abstract protected function getActionEntityCodename(): string;

    /**
     * @return string
     */
    abstract protected function getAppEntityCodename(): string;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    public function isConfirmed(ServerRequestInterface $request): bool
    {
        return ServerRequestHelper::getUser($request)->isEmailNotificationAllowed();
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function confirm(ServerRequestInterface $request): void
    {
        $userModel = ServerRequestHelper::getUser($request);
        $userModel->enableEmailNotification();
        $this->userRepo->save($userModel);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function sendEmail(ServerRequestInterface $request): void
    {
        $userModel = ServerRequestHelper::getUser($request);

        $notificationTarget = $this
            ->notification
            ->emailTarget($userModel->getEmail(), '', $userModel->getLanguageName());

        $ttl        = new \DateInterval($this->getTokenPeriod());
        $tokenModel = $this->tokenService->create($userModel, $ttl);

        $urlHelper        = ServerRequestHelper::getUrlHelper($request);
        $actionUrlElement = $urlHelper->getUrlElementByCodename(ConfirmAction::codename());
        $actionUrlParams  = $urlHelper->createUrlContainer()->setEntity($tokenModel);
        $actionUrl        = $urlHelper->makeUrl($actionUrlElement, $actionUrlParams, false);

        $appUrl        = $this->getAppUrl($request);
        $appWwwAddress = $this->makeAppWwwAddress($appUrl);

        $this->notification->directMessage('verification/email', $notificationTarget, [
            'subject'         => 'notification.verification.email.subj',
            'action_url'      => $actionUrl,
            'app_url'         => $appUrl,
            'app_www_address' => $appWwwAddress,
        ]);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getActionUrl(ServerRequestInterface $request): string
    {
        return $this->makeEntityUrl($request, $this->getActionEntityCodename());
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getAppUrl(ServerRequestInterface $request): string
    {
        return $this->makeEntityUrl($request, $this->getAppEntityCodename());
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string                                   $entityCodename
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function makeEntityUrl(ServerRequestInterface $request, string $entityCodename): string
    {
        $urlHelper  = ServerRequestHelper::getUrlHelper($request);
        $urlElement = $urlHelper->getUrlElementByCodename($entityCodename);

        return $urlHelper->makeUrl($urlElement);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function makeAppWwwAddress(string $url): string
    {
        $url       = trim($url, '/?&#');
        $urlScheme = parse_url($url, PHP_URL_SCHEME);
        if ($urlScheme) {
            $url = str_replace($urlScheme.'://', '', $url);
        }
        $url = 'www.'.$url;

        return $url;
    }

}
