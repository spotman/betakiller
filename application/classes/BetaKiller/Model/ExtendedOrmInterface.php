<?php
namespace BetaKiller\Model;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Spotman\Api\ApiResponseItemInterface;

interface ExtendedOrmInterface extends OrmInterface, ApiResponseItemInterface, DispatchableEntityInterface {}
