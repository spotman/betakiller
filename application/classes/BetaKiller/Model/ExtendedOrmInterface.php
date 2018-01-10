<?php
namespace BetaKiller\Model;

use BetaKiller\Search\ApplicableSearchModelInterface;
use BetaKiller\Search\SearchResultsItemInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

interface ExtendedOrmInterface extends
    OrmInterface,
    DispatchableEntityInterface,
    SearchResultsItemInterface,
    ApplicableSearchModelInterface
{
}
