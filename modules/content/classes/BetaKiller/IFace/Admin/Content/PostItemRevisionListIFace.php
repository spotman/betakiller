<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostItemRevisionListIFace extends AbstractContentAdminIFace
{
    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * PostItemRevisionList constructor.
     *
     * @param \BetaKiller\Helper\UrlHelper $urlHelper
     */
    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

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

        foreach ($post->getAllRevisions() as $revision) {
            $data[] = [
                'id'         => $revision->getID(),
                'diff_url'   => $this->urlHelper->getReadEntityUrl($revision, ZoneInterface::ADMIN),
                'is_actual'  => $post->isActualRevision($revision),
                'created_at' => $revision->getCreatedAt()->format('d.m.Y H:i:s'),
                'created_by' => $revision->getCreatedBy()->getUsername(),
            ];
        }

        return [
            'revisions' => $data,
        ];
    }
}
