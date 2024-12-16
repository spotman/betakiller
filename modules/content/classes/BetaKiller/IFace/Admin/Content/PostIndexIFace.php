<?php

namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\ContentPostRepository;
use BetaKiller\Url\Zone;
use Psr\Http\Message\ServerRequestInterface;

readonly class PostIndexIFace extends AbstractContentAdminIFace
{
    /**
     * PostIndex constructor.
     *
     * @param \BetaKiller\Repository\ContentPostRepository $postRepo
     */
    public function __construct(private ContentPostRepository $postRepo)
    {
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     * @uses \BetaKiller\IFace\Admin\Content\PostCreateIFace
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
                'url'   => $urlHelper->getReadEntityUrl($article, Zone::admin()),
                'label' => $article->getLabel(),
            ];
        }

        return [
            'createUrl' => $urlHelper->makeCodenameUrl(PostCreateIFace::codename()),
            'posts'     => $data,
        ];
    }
}
