<?php
namespace BetaKiller\Search\Provider;

use BetaKiller\Utils;
use BetaKiller\Search\Provider;

abstract class Factory {

    use Utils\Instance\Simple,
        Utils\Factory\Namespaced;

    /**
     * @param $name
     * @return \BetaKiller\Search\Provider
     */
    public function create($name)
    {
        return $this->_create($name);
    }

}
