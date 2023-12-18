<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use Throwable;

class DebugHelper
{
    public static function findNearestStackTraceItem(string $exclude, Throwable $e = null): StackTraceItem
    {
        $bt = $e ? $e->getTrace() : debug_backtrace();

        $i = 1;

        do {
            $item = StackTraceItem::fromArray($bt[$i]);
            $file = mb_strtolower(basename($item->file));
            $i++;
        } while (str_contains($file, $exclude) && isset($bt[$i]));

        return $item;
    }
}
