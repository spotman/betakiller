<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\TextHelper;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactFilesystemMonitor\FilesystemMonitorFactory;
use Symfony\Component\Process\Process;

final class FsWatcher
{
    private const WATCH_EXTENSIONS = [
        'php', // All
        'twig', // Compiled templates
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
                    if (TextHelper::contains($path, DIRECTORY_SEPARATOR.$ignoreDir.DIRECTORY_SEPARATOR)) {
                        return;
                    }
                }

                // Skip removed files
                if (!\file_exists($path)) {
                    return;
                }

                $ext = pathinfo($path, PATHINFO_EXTENSION);

                // Skip logs/git/frontend update
                if (!in_array($ext, self::WATCH_EXTENSIONS, true)) {
                    return;
                }

                // Skip files with fatal errors to prevent restart with a broken code
                if ($ext === 'php' && $this->hasFatalErrors($path)) {
                    return;
                }

                $this->logger->debug('FS WATCHER :msg', [
                    ':msg' => sprintf("%s: %s\n", $event, $path),
                ]);

                if ($this->watchTimer) {
                    $loop->cancelTimer($this->watchTimer);
                }

                // Throttling for 1 second
                $this->watchTimer = $loop->addTimer(1, static function () use ($path, $onChange) {
                    $onChange($path);
                });
            });

        $this->fsWatcher->start($loop);
    }

    private function hasFatalErrors(string $path): bool
    {
        /** @see https://stackoverflow.com/a/13224603 */
        $cmd = [\PHP_BINARY, '-l', '-f', $path];

        $process = new Process($cmd);

        $process->run();

        return !$process->isSuccessful() || !TextHelper::contains($process->getOutput(), 'No syntax errors detected');
    }
}
