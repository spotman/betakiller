<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\WampConfigInterface;
use BetaKiller\Wamp\InternalAuthProviderClient;
use BetaKiller\Wamp\WampRouter;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Thruway\Authentication\AuthenticationManager;
use Thruway\Authentication\WampCraAuthProvider;
use Thruway\Authentication\WampCraUserDbInterface;
use Thruway\Logging\Logger;
use Thruway\Transport\RatchetTransportProvider;
use function React\Promise\resolve;
use function React\Promise\reject;

final class WampRouterDaemon extends AbstractDaemon
{
    public const CODENAME = 'WampRouter';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Config\WampConfigInterface
     */
    private $wampConfig;

    /**
     * @var \Thruway\Authentication\WampCraUserDbInterface
     */
    private $wampUserDb;

    /**
     * @var \Thruway\Peer\RouterInterface
     */
    private $router;

    /**
     * @param \BetaKiller\Config\WampConfigInterface         $wampConfig
     * @param \Thruway\Authentication\WampCraUserDbInterface $wampUserDb
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(
        WampConfigInterface $wampConfig,
        WampCraUserDbInterface $wampUserDb,
        LoggerInterface $logger
    ) {
        $this->wampConfig = $wampConfig;
        $this->wampUserDb = $wampUserDb;
        $this->logger     = $logger;
    }

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        Logger::set($this->logger);

        $this->router = new WampRouter($loop);

        if (!$this->wampConfig->hasServerHost()) {
            return reject();
        }

        // Transport
        $transport = new RatchetTransportProvider(
            $this->wampConfig->getServerHost(),
            $this->wampConfig->getServerPort()
        );
        $transport->enableKeepAlive($loop);
        $this->router->addTransportProvider($transport);

        // Auth manager
        $authMgr = new AuthenticationManager();
        $this->router->registerModule($authMgr);

        // External auth
        $extAuth = new WampCraAuthProvider(['*'], $loop);
        $extAuth->setUserDb($this->wampUserDb);
        $this->router->addInternalClient($extAuth);

        // Internal auth
        $intAuth = new InternalAuthProviderClient(['*'], $loop);
        $this->router->addInternalClient($intAuth);

//        // Restart every 24h coz of annoying memory leak
//        $loop->addTimer(60 * 1440, function () use ($loop) {
//            $this->logger->info('Stopping router to prevent memory leaks');
//            $this->stopDaemon($loop);
//            $loop->stop();
//        });

        // Prepare to start (loop would be launched by the Runner task)
        $this->router->start(false);

        return resolve();
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        $this->router->stop(true);

        return resolve();
    }
}
