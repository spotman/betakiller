<?php
namespace BetaKiller\Url;


use BetaKiller\Model\DispatchableEntityInterface;

class ResolvingUrlContainer extends UrlContainer
{
    /**
     * @param string $key
     *
     * @return \BetaKiller\Url\UrlParameterInterface|null
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
     * @return \BetaKiller\Url\UrlParameterInterface|null
     */
    private function findParameterByKeyRecursive(array $params, string $key): ?UrlParameterInterface
    {
        foreach ($params as $param) {
            if ($this->isKey($param, $key)) {
                return $param;
            }

            if ($param instanceof DispatchableEntityInterface) {
                $linkedParam = $this->findParameterByKeyRecursive($param->getLinkedEntities(), $key);

                if ($linkedParam) {
                    return $linkedParam;
                }
            }
        }

        return null;
    }
}
