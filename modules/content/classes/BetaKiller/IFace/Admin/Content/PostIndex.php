<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\IFaceFactory;
use BetaKiller\Helper\ContentHelper;

class PostIndex extends AbstractAdminBase
{
    /**
     * @var ContentHelper
     * @Inject
     */
    private $contentHelper;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        $postRepo = $this->contentHelper->getPostRepository();

        // TODO deal with pages
        $articles = $postRepo->getAllArticles();

        $data = [];

        foreach ($articles as $article) {
            $data[] = [
                'id'          => $article->get_id(),
                'url'         => $this->ifaceHelper->getReadEntityUrl($article),
                'label'       => $article->getLabel(),
            ];
        }

        /** @var \BetaKiller\IFace\Admin\Content\PostCreate $createPostIFace */
        $createPostIFace = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_PostCreate');

        return [
            'createUrl' => $createPostIFace->url(),
            'posts' => $data,
        ];
    }
}
