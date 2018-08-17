<?php
declare(strict_types=1);

namespace Worknector\WebHook\Test;

use BetaKiller\WebHook\Test\AbstractDummyWebHook;

class DummyFailed extends AbstractDummyWebHook
{
    public function process(): void
    {
        throw new \RuntimeException('Test error');
    }
}
