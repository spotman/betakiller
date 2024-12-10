<?php

declare(strict_types=1);

use BetaKiller\Url\Zone;

return [
    Zone::public(),
    Zone::personal(),
    Zone::admin(),
    Zone::developer(),
    Zone::preview(),
];
