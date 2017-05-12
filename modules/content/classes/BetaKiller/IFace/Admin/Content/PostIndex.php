<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\IFace\IFaceFactory;

class PostIndex extends AdminBase
{
    /**
     * @var \BetaKiller\IFace\IFaceFactory
     */
    private $ifaceFactory;

    /**
     * PostIndex constructor.
     *
     * @param \BetaKiller\IFace\IFaceFactory $ifaceFactory
     */
    public function __construct(IFaceFactory $ifaceFactory)
    {
        $this->ifaceFactory = $ifaceFactory;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        $articles = $this->model_factory_content_post()->getAllArticles();

        $data = [];

        foreach ($articles as $article) {
            $data[] = [
                'id'          => $article->get_id(),
                'url'         => $article->get_admin_url(),
                'label'       => $article->getLabel(),
            ];
        }

        /** @var \BetaKiller\IFace\Admin\Content\PostCreate $createPostIFace */
        $createPostIFace = $this->ifaceFactory->from_codename('Admin_Content_PostCreate');

        return [
            'createUrl' => $createPostIFace->url(),
            'posts' => $data,
        ];
    }
}
