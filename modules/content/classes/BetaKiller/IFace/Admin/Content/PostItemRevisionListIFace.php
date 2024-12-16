<?php

namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\Zone;
use Psr\Http\Message\ServerRequestInterface;

readonly class PostItemRevisionListIFace extends AbstractContentAdminIFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $post = ContentUrlContainerHelper::getContentPost($request);

        $data = [];

        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        foreach ($post->getAllRevisions() as $revision) {
            $data[] = [
                'id'         => $revision->getID(),
                'diff_url'   => $urlHelper->getReadEntityUrl($revision, Zone::admin()),
                'is_actual'  => $post->isActualRevision($revision),
                'created_at' => $revision->getCreatedAt()->format('d.m.Y H:i:s'),
                'created_by' => $revision->getCreatedBy()->getFullName(),
            ];
        }

        return [
            'revisions' => $data,
        ];
    }
}
