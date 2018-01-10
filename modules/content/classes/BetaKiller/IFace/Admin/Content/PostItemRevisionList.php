<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Url\UrlDispatcherException;

class PostItemRevisionList extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * PostItemRevisionList constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlContainerHelper $urlParametersHelper
     */
    public function __construct(ContentUrlContainerHelper $urlParametersHelper)
    {
        $this->urlParametersHelper = $urlParametersHelper;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        $post = $this->urlParametersHelper->getContentPost();

        if (!$post) {
            throw new UrlDispatcherException('Missing ContentPost model');
        }

        $data = [];
        $revisions = $post->getAllRevisions();

        foreach ($revisions as $revision) {
            $data[] = [
                'id' => $revision->getID(),
                'is_actual' => $post->isActualRevision($revision),
                'created_at' => $revision->getCreatedAt()->format('d.m.Y H:i:s'),
                'created_by' => $revision->getCreatedBy()->getUsername(),
            ];
        }

        return [
            'revisions' => $data,
        ];
    }
}
