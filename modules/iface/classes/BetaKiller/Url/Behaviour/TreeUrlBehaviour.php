<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Url\UrlBehaviourException;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlPathIterator;

class TreeUrlBehaviour extends MultipleUrlBehaviour
{
    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\IFace\IFaceModelInterface      $model
     * @param \BetaKiller\Url\UrlPathIterator            $it
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return bool
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \OutOfRangeException
     */
    public function parseUri(
        IFaceModelInterface $model,
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
}
