<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\ContentPostRepository;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostIndex extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Repository\ContentPostRepository
     */
    private $postRepo;

    /**
     * PostIndex constructor.
     *
     * @param \BetaKiller\Repository\ContentPostRepository $postRepo
     */
    public function __construct(ContentPostRepository $postRepo)
    {
        $this->postRepo = $postRepo;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     * @uses \BetaKiller\IFace\Admin\Content\PostCreate
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        // TODO deal with pages
        $articles = $this->postRepo->getAllArticles();

        $data = [];

        foreach ($articles as $article) {
            $data[] = [
                'id'    => $article->getID(),
                'url'   => $urlHelper->getReadEntityUrl($article, ZoneInterface::ADMIN),
                'label' => $article->getLabel(),
            ];
        }

        $createPostIFace = $urlHelper->getUrlElementByCodename('Admin_Content_PostCreate');

        return [
            'createUrl' => $urlHelper->makeUrl($createPostIFace),
            'posts'     => $data,
        ];
    }
}
