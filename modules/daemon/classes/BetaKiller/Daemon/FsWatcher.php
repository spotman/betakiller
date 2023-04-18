<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Exception;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\TextHelper;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Promise;
use ReactFilesystemMonitor\FilesystemMonitorFactory;
use ReactFilesystemMonitor\FilesystemMonitorInterface;
use Symfony\Component\Process\Process;
use function React\Async\await;

final class FsWatcher
{
    private const WATCH_EXTENSIONS = [
        'php', // All
        'twig', // Compiled templates
        'xml', // Configs
        'yml', // I18n
    ];

    private const WATCH_IGNORE_DIRS = [
        'cache',
        'logs',
        'i18n',
    ];

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \ReactFilesystemMonitor\FilesystemMonitorInterface|null
     */
    private ?FilesystemMonitorInterface $fsWatcher = null;

    /**
     * @var \React\EventLoop\TimerInterface|null
     */
    private ?TimerInterface $watchTimer = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var string[]
     */
    private array $brokenPhpFiles = [];

    private bool $isStarted = false;

    /**
     * FsWatcher constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface $appEnv
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->logger = $logger;
    }

    public function start(LoopInterface $loop, callable $onChange): void
    {
        $appPath = $this->appEnv->getAppRootPath();

        if ($this->fsWatcher) {
            throw new Exception('Watcher is already started for :path', [
                ':path' => $appPath,
            ]);
        }

        $this->watchDir($appPath, $loop, $onChange);
    }

    public function stop(LoopInterface $loop): void
    {
        if (!$this->fsWatcher) {
            return;
        }

        $promise = new Promise(function ($resolve) use ($loop) {
            $loop->addPeriodicTimer(0.2, function (TimerInterface $timer) use ($loop, $resolve) {
                if ($this->isStarted) {
                    return;
                }

                $loop->cancelTimer($timer);
                $resolve();
            });
        });

        $this->fsWatcher->stop();

        // Block until done
        await($promise);
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

                $isStored = isset($this->brokenPhpFiles[$path]);

                // Skip removed files
                if (!\file_exists($path)) {
                    if ($isStored) {
                        // Exclude removed file
                        unset($this->brokenPhpFiles[$path]);
                    }

                    return;
                }

                $ext = pathinfo($path, PATHINFO_EXTENSION);

                // Skip logs/git/frontend update
                if (!in_array($ext, self::WATCH_EXTENSIONS, true)) {
                    return;
                }

                // Skip files with fatal errors to prevent restart with a broken code
                if ($ext === 'php') {
                    $isBroken = $this->hasFatalErrors($path);

                    // No action on broken files changes
                    if ($isBroken) {
                        // Store broken file
                        if (!$isStored) {
                            $this->brokenPhpFiles[$path] = time();
                        }

                        return;
                    }

                    // Stored but not broken
                    if ($isStored) {
                        // Remove fixed file
                        unset($this->brokenPhpFiles[$path]);
                    }
                }

                // Prevent restart until all broken files are resolved
                if ($this->brokenPhpFiles) {
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

        $this->fsWatcher->on('error', function (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);

            $this->isStarted = false;
        });

        $this->fsWatcher->on('stop', function () {
            $this->isStarted = false;
        });

        $this->fsWatcher->start($loop);

        $this->isStarted = true;
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
