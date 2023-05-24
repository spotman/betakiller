<?php
declare(strict_types=1);

namespace BetaKiller\Log;

use BetaKiller\ModuleInitializerInterface;

final class LogModuleInitializer implements ModuleInitializerInterface
{
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function initModule(): void
    {
        // Proxy old Kohana logs to new logging subsystem
        \Kohana::$log->attach(new KohanaLogProxy($this->logger));
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
