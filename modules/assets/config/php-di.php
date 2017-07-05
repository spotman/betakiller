<?php

use BetaKiller\Assets\MultiLevelPath;

return [

    'definitions' => [

        // Allow independent configuration for url strategy and storage
        MultiLevelPath::class => DI\object(MultiLevelPath::class)->scope(\DI\Scope::PROTOTYPE),

    ],

];
