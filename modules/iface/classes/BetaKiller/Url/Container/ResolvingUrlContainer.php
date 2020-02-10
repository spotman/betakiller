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
            // Track checked items
            $checked = [];

            $params = array_reverse($this->getAllParameters());

            // Start from the last parameter, it`s more specific coz added later
            $instance = $this->findParameterByKeyRecursive($params, $key, $checked);

            if ($instance) {
                $this->setParameter($instance);
            }
        }

        return $instance;
    }

    /**
     * @param UrlParameterInterface[] $params
     * @param string                  $key
     *
     * @param array                   $checked
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface|null
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    private function findParameterByKeyRecursive(array $params, string $key, array &$checked): ?UrlParameterInterface
    {
        if (count($checked) > 50) {
            throw new UrlContainerException(
                'Too much items checked while resoling ":key", checked: ":checked"', [
                ':key'     => $key,
                ':checked' => implode('", "', $checked),
            ]);
        }

        $skipped = 0;

        // Check highest level first
        foreach ($params as $param) {
            $with = $param::getUrlContainerKey();

            // Prevent endless loop on circular dependencies
            if (\in_array($with, $checked, true)) {
                $skipped++;
                continue;
            }

            $checked[] = $with;

            if ($this->isKey($param, $key)) {
                return $param;
            }
        }

        // Prevent deep search if all params were skipped
        if (count($params) === $skipped) {
            return null;
        }

        // Go deeper
        foreach ($params as $param) {
            if ($param instanceof DispatchableEntityInterface) {
                $linkedParam = $this->findParameterByKeyRecursive($param->getLinkedEntities(), $key, $checked);

                if ($linkedParam) {
                    return $linkedParam;
                }
            }
        }

        return null;
    }
}
