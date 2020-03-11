<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Helper\AppEnvInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactFilesystemMonitor\FilesystemMonitorFactory;
// use ReactFilesystemMonitor\FilesystemMonitorInterface;

final class FsWatcher
{
    private const WATCH_EXTENSIONS = [
        'php', // All
        'xml', // Configs
        'yml',
    ];

    private const WATCH_IGNORE_DIRS = [
        'cache',
        'logs',
        'i18n',
    ];

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \ReactFilesystemMonitor\FilesystemMonitorInterface|null
     */
    private $fsWatcher;

    /**
     * @var \React\EventLoop\TimerInterface|null
     */
    private $watchTimer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * FsWatcher constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->logger = $logger;
    }

    public function start(LoopInterface $loop, callable $onChange): void
    {
        $appPath = $this->appEnv->getAppRootPath();

        $this->watchDir($appPath, $loop, $onChange);
    }

    public function stop(): void
    {
        if ($this->fsWatcher) {
            $this->fsWatcher->stop();
        }
    }

    private function watchDir(string $path, LoopInterface $loop, callable $onChange): void
    {
        $this->logger->debug('Starting watcher for :path', [
            ':path' => $path,
        ]);

        $this->fsWatcher = (new FilesystemMonitorFactory())->create($path, [
            'create',
            'modify',
            'delete',
            'move_from',
            'move_to',
        ]);

        $this->fsWatcher->on(
            'all',
            function (string $path, bool $isDir, string $event /*, FilesystemMonitorInterface $monitor */) use (
                $onChange,
                $loop
            ) {
                // Skip directory events
                if ($isDir) {
                    return;
                }

                // Skip files in ignored directories
                foreach (self::WATCH_IGNORE_DIRS as $ignoreDir) {
                    if (strpos($path, DIRECTORY_SEPARATOR.$ignoreDir.DIRECTORY_SEPARATOR) !== false) {
                        return;
                    }
                }

                $ext = pathinfo($path, PATHINFO_EXTENSION);

                // Skip logs/git/frontend update
                if (!in_array($ext, self::WATCH_EXTENSIONS, true)) {
                    return;
                }

                $this->logger->debug('FS WATCHER :msg', [
                    ':msg' => sprintf("%s:  %s%s\n", $event, $path, $isDir ? ' [dir]' : ''),
                ]);

                if ($this->watchTimer) {
                    $loop->cancelTimer($this->watchTimer);
                }

                // Throttling for 1 second
                $this->watchTimer = $loop->addTimer(1, function () use ($onChange /*, $monitor */) {
                    // Prevent repeated events
//                    $monitor->stop();

                    $onChange();
                });
            });

        $this->fsWatcher->start($loop);
    }
}
