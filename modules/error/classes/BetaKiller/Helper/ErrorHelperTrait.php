<?php
namespace BetaKiller\Helper;

use BetaKiller\Error\PhpExceptionStorage;

trait ErrorHelperTrait
{
    use IFaceTrait;

    /**
     * @return \BetaKiller\Error\PhpExceptionStorageInterface
     */
    protected function phpExceptionStorageFactory()
    {
        return new PhpExceptionStorage;
    }

    /**
     * @return \BetaKiller\Utils\Kohana\ORM\OrmInterface|\Model_PhpException
     */
    protected function phpExceptionModelFactory()
    {
        return \ORM::factory('PhpException');
    }

    /**
     * @return \BetaKiller\Utils\Kohana\ORM\OrmInterface|\Model_PhpExceptionHistory
     */
    protected function phpExceptionHistoryModelFactory()
    {
        return \ORM::factory('PhpExceptionHistory');
    }

    /**
     * @return \BetaKiller\Error\PhpExceptionModelInterface|null
     */
    protected function phpExceptionUrlParameter()
    {
        return $this->url_parameters()->get(\Model_PhpException::URL_PARAM);
    }
}
