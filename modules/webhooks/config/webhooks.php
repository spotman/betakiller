<?php
declare(strict_types=1);

use BetaKiller\WebHook\Test\AbstractDummyWebHook;

return [
    // External service name
    AbstractDummyWebHook::SERVICE_NAME => [

        // Event name => WebHook codename

        // Test
        'TestSucceeded' => 'Test_DummySucceeded',
        'TestFailed'    => 'Test_DummyFailed',

    ],
];
