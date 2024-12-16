<?php

namespace BetaKiller\IFace\Admin\Content;

use Psr\Http\Message\ServerRequestInterface;

readonly class CommentAggregateByStatusIFace extends AbstractContentAdminIFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        // TODO get aggregated statuses list

        return [];
    }
}
