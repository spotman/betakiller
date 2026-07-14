<?php

declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Log\LoggerInterface;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DebugBar;
use DebugBar\Storage\StorageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

readonly class DebugBarFactory implements DebugBarFactoryInterface
{
    public function __construct(
        private StorageInterface $storage,
        private Environment $twigEnv,
        private LoggerInterface $logger
    ) {
    }

    public function create(ServerRequestInterface $request, string $baseUrl): DebugBar
    {
        $debugBar = new DebugBar();

        $session = ServerRequestHelper::getSession($request);

        // Initialize http driver
        $debugBar->setHttpDriver(new DebugBarHttpDriver($session));

        // Common collectors
        $debugBar
            ->addCollector(new DebugBarTimeDataCollector($request))
            ->addCollector(new DebugBarRequestDataCollector($request))
            ->addCollector(new DebugBarCookiesDataCollector($request))
            ->addCollector(new DebugBarSessionDataCollector($request));

        if (SessionHelper::hasUserID($session)) {
            $debugBar->addCollector(new DebugBarUserDataCollector($request));

            $user = ServerRequestHelper::getUser($request);

            // Developer collectors
            if ($user->isDeveloper()) {
                $debugBar
                    ->addCollector(new MemoryCollector())
                    ->addCollector(new DebugBarTwigDataCollector($this->twigEnv))
                    ->addCollector(new MonologCollector($this->logger->getMonologInstance()));
            }
        }

        $debugBar->setStackDataSessionNamespace(SessionHelper::makeServiceKey('stack_data'));

        // Storage for processing data for AJAX calls and redirects
        $debugBar->setStorage($this->storage);

        // Prepare renderer
        $renderer = $debugBar
            ->getJavascriptRenderer($baseUrl)
            ->setOpenHandlerUrl($baseUrl);

        // No jQuery
        $renderer->setEnableJqueryNoConflict(false);

        $renderer
            ->setBindAjaxHandlerToXHR()
            ->setBindAjaxHandlerToFetch();

        // UI fix
        $renderer->addInlineAssets([
            '.phpdebugbar-widgets-measure:hover { background: #dcdbdb }'.
            '.phpdebugbar-widgets-measure:hover .phpdebugbar-widgets-label { color: #222 !important }'.
            '.phpdebugbar-widgets-measure:hover .phpdebugbar-widgets-value { background: #009bda }'.
            'div.phpdebugbar-header, a.phpdebugbar-restore-btn { background: #efefef }'.
            'div.phpdebugbar-header { padding-left: 0 }'.
            'a.phpdebugbar-restore-btn { text-align: center }'.
            'a.phpdebugbar-restore-btn:before { content: "{}"; font-size: 16px; color: #333; font-weight: bold }',
        ], [], []);

        return $debugBar;
    }
}
