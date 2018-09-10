<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Repository\ContentPostRepository;

class PostIndex extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Repository\ContentPostRepository
     */
    private $postRepo;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * PostIndex constructor.
     *
     * @param \BetaKiller\Repository\ContentPostRepository $postRepo
     * @param \BetaKiller\Helper\IFaceHelper               $ifaceHelper
     */
    public function __construct(
        ContentPostRepository $postRepo,
        IFaceHelper $ifaceHelper
    ) {
        $this->postRepo    = $postRepo;
        $this->ifaceHelper = $ifaceHelper;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        // TODO deal with pages
        $articles = $this->postRepo->getAllArticles();

        $data = [];

        foreach ($articles as $article) {
            $data[] = [
                'id'    => $article->getID(),
                'url'   => $this->ifaceHelper->getReadEntityUrl($article),
                'label' => $article->getLabel(),
            ];
        }

        /** @var \BetaKiller\IFace\Admin\Content\PostCreate $createPostIFace */
        $createPostIFace = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_PostCreate');

        return [
            'createUrl' => $this->ifaceHelper->makeIFaceUrl($createPostIFace),
            'posts'     => $data,
        ];
    }
}
