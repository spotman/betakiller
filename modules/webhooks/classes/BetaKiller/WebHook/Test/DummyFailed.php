<?php
declare(strict_types=1);

namespace BetaKiller\WebHook\Test;

class DummyFailed extends AbstractDummyWebHook
{
    public function process(): void
    {
        throw new \RuntimeException('Test error');
    }
}
