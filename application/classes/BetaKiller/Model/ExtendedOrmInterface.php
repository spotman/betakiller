<?php
namespace BetaKiller\Model;

use BetaKiller\Search\ApplicableModelInterface;
use BetaKiller\Search\SearchResultsItemInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Spotman\Api\ApiResponseItemInterface;

interface ExtendedOrmInterface
    extends OrmInterface, ApiResponseItemInterface, DispatchableEntityInterface, ApplicableModelInterface, SearchResultsItemInterface
{
    public function getValidationExceptionErrors(\ORM_Validation_Exception $e): array;
}
