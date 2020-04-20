<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Helper\CookieHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Session\DatabaseSessionStorage;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

class WampClientHelper
{
    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $cookieHelper;

    /**
     * @var \BetaKiller\Wamp\WampSessionStorage
     */
    private $sessionStorage;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \React\EventLoop\TimerInterface
     */
    private $timer;

    /**
     * @var bool
     */
    private bool $isCacheEnabled = false;

    /**
     * @param \BetaKiller\Service\AuthService     $auth
     * @param \BetaKiller\Helper\CookieHelper     $cookieHelper
     * @param \BetaKiller\Wamp\WampSessionStorage $sessionStorage
     * @param \Psr\Log\LoggerInterface            $logger
     */
    public function __construct(
        AuthService $auth,
        CookieHelper $cookieHelper,
        WampSessionStorage $sessionStorage,
        LoggerInterface $logger
    ) {
        $this->auth           = $auth;
        $this->cookieHelper   = $cookieHelper;
        $this->sessionStorage = $sessionStorage;
        $this->logger         = $logger;
    }

    public function enableCaching(): void
    {
        $this->isCacheEnabled = true;
    }

    /**
     * @param \React\EventLoop\LoopInterface $loop
     */
    public function bindSessionHandlers(LoopInterface $loop): void
    {
        if (!$this->isCacheEnabled) {
            throw new \LogicException('Binding session handlers is useless without caching enabled');
        }

        if (!$this->timer) {
            $this->timer = $loop->addPeriodicTimer(300, function () {
                // Remove old sessions without connections
                $this->removeDeadSessions();
            });
        }
    }

    public function getProcedureSession(array $callArgs): WampSession
    {
        $options = (array)$callArgs[2];

        $authID = (string)$options['authid'];

        if (!$authID) {
            throw new \LogicException('Empty session id in wamp procedure call');
        }

        return $this->getSessionByAuthID($authID);
    }

    public function getSessionUser(WampSession $wampSession): UserInterface
    {
        $userSession = $wampSession->getUserSession();

        return $this->auth->getSessionUser($userSession);
    }

    private function getSessionByAuthID(string $authID): WampSession
    {
        if ($authID === 'anonymous') {
            throw new \LogicException('Anonymous is not allowed');
        }

        $wampSession = $this->isCacheEnabled
            ? $this->sessionStorage->findByAuthID($authID)
            : null;

        // Initialize WAMP session if not exists
        if (!$wampSession) {
            $sessionID   = $this->cookieHelper->decodeValue(DatabaseSessionStorage::COOKIE_NAME, $authID);
            $userSession = $this->auth->getSession($sessionID);
            $wampSession = new WampSession($authID, $userSession);

            if ($this->isCacheEnabled) {
                $this->sessionStorage->add($wampSession);
            }
        }

        // Mark session as used
        $wampSession->touched();

        return $wampSession;
    }

    private function removeDeadSessions(): void
    {
        foreach ($this->sessionStorage->findDeadSessions() as $session) {
            $this->logger->info('Removing dead wamp session :id', [
                ':id' => $session->getID(),
            ]);

            $this->sessionStorage->remove($session);
        }
    }
}
