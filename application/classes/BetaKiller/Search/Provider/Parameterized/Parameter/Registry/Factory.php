<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 02.11.15
 * Time: 22:02
 */

namespace BetaKiller\Search\Provider\Parameterized\Parameter\Registry;

use BetaKiller\Search\Provider\Parameterized\Parameter\Registry;
use BetaKiller\Utils;
use BetaKiller\Utils\Factory\Exception;

abstract class Factory
{
    use Utils\Factory\Namespaced;
    use Utils\Instance\Simple;

    /**
     * Factory method
     *
     * @param $name
     * @return Registry
     */
    public function create($name)
    {
        return $this->_create($name);
    }
}
