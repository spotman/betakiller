<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use Throwable;

class DebugHelper
{
    public static function findNearestStackTraceItem(string $exclude, Throwable $e = null): StackTraceItem
    {
        $bt = $e ? $e->getTrace() : debug_backtrace();

        $i = count($bt) > 1 ? 1 : 0;

        do {
            $item = StackTraceItem::fromArray($bt[$i]);
            $file = mb_strtolower(basename($item->file));
            $callee = mb_strtolower($item->getCallee());
            $i++;

            $isExcluded = str_contains($file, $exclude) || str_contains($callee, $exclude);
        } while ($isExcluded && isset($bt[$i]));

        return $item;
    }
}
