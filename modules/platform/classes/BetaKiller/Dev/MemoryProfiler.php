<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\AppEnvInterface;
use Psr\Log\LoggerInterface;

class MemoryProfiler
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * MemoryProfiler constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->logger = $logger;
    }

    public function dump(string $codename): void
    {
        // Exit early if profiling is not enabled in the current env
        if (!\getenv('MEMPROF_PROFILE')) {
            return;
        }

        if (!\function_exists('memprof_enabled')) {
            $this->logger->error('MemProf is not installed');

            return;
        }

        if (!\memprof_enabled()) {
            $this->logger->error('Memory profiling is not enabled');

            return;
        }

        $path = $this->appEnv->getTempPath(sprintf('cachegrind.%s.%d.out', $codename, time()));

        $resource = fopen($path, 'wb+');

        \memprof_dump_callgrind($resource);

        fclose($resource);

        $this->logger->notice('Memory dumped to :path', [
            ':path' => $path,
        ]);
    }
}
