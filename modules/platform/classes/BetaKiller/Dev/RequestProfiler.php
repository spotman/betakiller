<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Stopwatch\StopwatchEvent;

final class RequestProfiler extends AbstractProfiler
{
    public static function begin(ServerRequestInterface $request, string $label): array
    {
        $event = self::fetch($request)?->start($label);

        return [$request, $event];
    }

    public static function end(array $pack): void
    {
        /** @var ServerRequestInterface $request */
        /** @var StopwatchEvent $event */
        [$request, $event] = $pack;

        if ($request && $event) {
            self::fetch($request)?->stop($event);
        }
    }

    public static function mark(ServerRequestInterface $request, string $label): void
    {
        self::fetch($request)?->start($label)->stop();
    }

    public static function inject(ServerRequestInterface $request, RequestProfiler $profiler): ServerRequestInterface
    {
        return $request->withAttribute(RequestProfiler::class, $profiler);
    }

    public static function fetch(ServerRequestInterface $request): ?RequestProfiler
    {
        return $request->getAttribute(RequestProfiler::class);
    }

    public static function getRequestStartTime(ServerRequestInterface $request): float
    {
        return $request->getServerParams()['REQUEST_TIME_FLOAT'] ?? $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
    }
}
