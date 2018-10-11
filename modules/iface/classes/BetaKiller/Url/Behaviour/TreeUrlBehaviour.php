<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlPathIterator;

class TreeUrlBehaviour extends MultipleUrlBehaviour
{
    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\Url\UrlElementInterface                  $model
     * @param \BetaKiller\Url\UrlPathIterator                      $it
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return bool
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function parseUri(
        UrlElementInterface $model,
        UrlPathIterator $it,
        UrlContainerInterface $params
    ): bool {
        // Empty url means nothing to process
        if (!$it->count()) {
            return false;
        }

        $absentFound = false;

        do {
            try {
                $this->parseUriParameterPart($model, $it, $params);
                $it->next();
            } /** @noinspection BadExceptionsProcessingInspection */
            catch (UrlBehaviourException $e) {
                $absentFound = true;

                // Move one step back so current uri part will be processed by the next iface
                $it->prev();
            }
        } while (!$absentFound && $it->valid());

        // Anyway we processed, so return true
        return true;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $ifaceModel
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    protected function getUri(UrlElementInterface $ifaceModel, UrlContainerInterface $params): string
    {
        return $this->prototypeService->getCompiledTreePrototypeValue($ifaceModel->getUri(), $params);
    }
}
