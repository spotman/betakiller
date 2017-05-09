<?php

use DI\Scope;
use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationMessageCommon;

return [

    'definitions'   =>  [

        // Basic notification message
        NotificationMessageInterface::class => DI\object(NotificationMessageCommon::class)
            ->scope(Scope::PROTOTYPE),

    ],

];
