<?php
declare(strict_types=1);

use BetaKiller\Daemon\ApiWorkerEightDaemon;
use BetaKiller\Daemon\ApiWorkerFiveDaemon;
use BetaKiller\Daemon\ApiWorkerFourDaemon;
use BetaKiller\Daemon\ApiWorkerNineDaemon;
use BetaKiller\Daemon\ApiWorkerOneDaemon;
use BetaKiller\Daemon\ApiWorkerSevenDaemon;
use BetaKiller\Daemon\ApiWorkerSixDaemon;
use BetaKiller\Daemon\ApiWorkerTenDaemon;
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
    ApiWorkerSixDaemon::CODENAME,
    ApiWorkerSevenDaemon::CODENAME,
    ApiWorkerEightDaemon::CODENAME,
    ApiWorkerNineDaemon::CODENAME,
    ApiWorkerTenDaemon::CODENAME,

    WampEsbBridgeDaemon::CODENAME,
    CommandBusWorkerDaemon::CODENAME,
];
