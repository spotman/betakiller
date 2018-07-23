<?php
declare(strict_types=1);

namespace BetaKiller\Url\Container;

use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;

class ResolvingUrlContainer extends UrlContainer
{
    /**
     * @param string $key
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface|null
     */
    public function getParameter(string $key): ?UrlParameterInterface
    {
        $instance = parent::getParameter($key);

        if (!$instance) {
            // Start from the last parameter, it`s more specific coz added later
            $instance = $this->findParameterByKeyRecursive(array_reverse($this->getAllParameters()), $key);

            if ($instance) {
                $this->setParameter($instance);
            }
        }

        return $instance;
    }

    /**
     * @param UrlParameterInterface[]  $params
     * @param string $key
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface|null
     */
    private function findParameterByKeyRecursive(array $params, string $key): ?UrlParameterInterface
    {
        foreach ($params as $param) {
            if ($this->isKey($param, $key)) {
                return $param;
            }

            if ($param instanceof DispatchableEntityInterface) {
                if (!$param->hasLinkedEntity($key)) {
                    continue;
                }

                $linkedParam = $this->findParameterByKeyRecursive($param->getLinkedEntities(), $key);

                if ($linkedParam) {
                    return $linkedParam;
                }
            }
        }

        return null;
    }
}
