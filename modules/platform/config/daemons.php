<?php
declare(strict_types=1);

use BetaKiller\Daemon\ApiWorkerFiveDaemon;
use BetaKiller\Daemon\ApiWorkerFourDaemon;
use BetaKiller\Daemon\ApiWorkerOneDaemon;
use BetaKiller\Daemon\ApiWorkerThreeDaemon;
use BetaKiller\Daemon\ApiWorkerTwoDaemon;
use BetaKiller\Daemon\CommandBusWorkerDaemon;
use BetaKiller\Daemon\WampEsbBridgeDaemon;

return [
    // Multiple APi workers in round-robin mode (workers are stateless)
    ApiWorkerOneDaemon::CODENAME,
    ApiWorkerTwoDaemon::CODENAME,
    ApiWorkerThreeDaemon::CODENAME,
    ApiWorkerFourDaemon::CODENAME,
    ApiWorkerFiveDaemon::CODENAME,

    WampEsbBridgeDaemon::CODENAME,
    CommandBusWorkerDaemon::CODENAME,
];
