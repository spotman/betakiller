<?php

declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Log\LoggerInterface;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DebugBar;
use DebugBar\Storage\FileStorage;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

readonly class DebugBarFactory implements DebugBarFactoryInterface
{
    public function __construct(
        private AppEnvInterface $appEnv,
        private Environment $twigEnv,
        private LoggerInterface $logger
    ) {
    }

    public function create(ServerRequestInterface $request, string $baseUrl): DebugBar
    {
        $debugBar = new DebugBar();

        $session = ServerRequestHelper::getSession($request);
        $user    = ServerRequestHelper::getUser($request);

        // Initialize http driver
        $debugBar->setHttpDriver(new DebugBarHttpDriver($session));

        // Developer collectors
        if ($user->isDeveloper()) {
            $debugBar
                ->addCollector(new DebugBarTimeDataCollector($request))
                ->addCollector(new DebugBarCookiesDataCollector($request));
        }

        // Common collectors
        $debugBar
            ->addCollector(new DebugBarSessionDataCollector($request))
            ->addCollector(new DebugBarUserDataCollector($request));

        // Developer collectors
        if ($user->isDeveloper()) {
            $debugBar
                ->addCollector(new MemoryCollector())
                ->addCollector(new DebugBarTwigDataCollector($this->twigEnv))
                ->addCollector(new MonologCollector($this->logger->getMonologInstance()));
        }

//        if (ServerRequestHelper::isHtmlPreferred($request)) {
//            $debugBar->addCollector(new DebugBarTwigDataCollector($this->twigEnv));
//        }

        $debugBar->setStackDataSessionNamespace(SessionHelper::makeServiceKey('stack_data'));

        // Storage for processing data for AJAX calls and redirects
        $debugBar->setStorage(new FileStorage($this->appEnv->getTempPath('debugbar-storage')));

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
