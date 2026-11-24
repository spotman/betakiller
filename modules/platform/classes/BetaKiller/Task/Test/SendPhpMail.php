<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Task\AbstractTask;
use Exception;

class SendPhpMail extends AbstractTask
{
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $address = getenv('DEBUG_EMAIL_ADDRESS');

        // Send plain message
        if (!mail($address, 'Test message from PHP mail() function', 'This is a test of PHP mail() subsystem')) {
            throw new Exception(sprintf('Can not send email to %s via PHP mail() function', $address));
        }
    }
}
