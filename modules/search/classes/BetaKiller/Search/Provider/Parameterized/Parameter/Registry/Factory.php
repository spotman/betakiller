<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 02.11.15
 * Time: 22:02
 */

namespace BetaKiller\Search\Provider\Parameterized\Parameter\Registry;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Search\Provider\Parameterized\Parameter\Registry;

abstract class Factory
{
    /**
     * Factory method
     *
     * @param $name
     *
     * @return Registry
     */
    public function create($name)
    {
        throw new NotImplementedHttpException;
    }
}
