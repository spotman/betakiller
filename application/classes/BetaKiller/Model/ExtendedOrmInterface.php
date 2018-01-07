<?php
namespace BetaKiller\Model;

use BetaKiller\Search\ApplicableSearchModelInterface;
use BetaKiller\Search\SearchResultsItemInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Spotman\Api\ApiResponseItemInterface;

interface ExtendedOrmInterface extends
    OrmInterface,
    ApiResponseItemInterface,
    DispatchableEntityInterface,
    SearchResultsItemInterface,
    ApplicableSearchModelInterface {}
