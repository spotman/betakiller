<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\IFaceFactory;

class PostIndex extends AbstractAdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        // TODO deal with pages
        $articles = $this->model_factory_content_post()->getAllArticles();

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
