<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Repository\ContentAttachmentRepository;
use BetaKiller\Repository\ContentImageRepository;
use BetaKiller\Repository\ContentPostThumbnailRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WordpressContentRedirectHandler implements RequestHandlerInterface
{
    /**
     * @var \BetaKiller\Repository\ContentImageRepository
     */
    private $imageRepo;

    /**
     * @var \BetaKiller\Repository\ContentPostThumbnailRepository
     */
    private $thumbRepo;

    /**
     * @var \BetaKiller\Repository\ContentAttachmentRepository
     */
    private $attachmentRepo;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * WordpressContentRedirectHandler constructor.
     *
     * @param \BetaKiller\Repository\ContentImageRepository         $imageRepo
     * @param \BetaKiller\Repository\ContentPostThumbnailRepository $thumbRepo
     * @param \BetaKiller\Repository\ContentAttachmentRepository    $attachmentRepo
     * @param \BetaKiller\Helper\AssetsHelper                       $assetsHelper
     */
    public function __construct(
        ContentImageRepository $imageRepo,
        ContentPostThumbnailRepository $thumbRepo,
        ContentAttachmentRepository $attachmentRepo,
        AssetsHelper $assetsHelper
    ) {
        $this->imageRepo      = $imageRepo;
        $this->thumbRepo      = $thumbRepo;
        $this->attachmentRepo = $attachmentRepo;
        $this->assetsHelper   = $assetsHelper;
    }

    /**
     * Handle the request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $file = $request->getAttribute('file');

        if (!$file) {
            throw new NotFoundHttpException;
        }

        $path = '/'.ltrim($request->getUri()->getPath(), '/');

        $model = $this->findContentModelByWpPath($path);

        if (!$model) {
            throw new NotFoundHttpException;
        }

        $url = $this->assetsHelper->getOriginalUrl($model);

        return ResponseHelper::permanentRedirect($url);
    }

    /**
     * @param string $path
     *
     * @return AssetsModelInterface|null
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    protected function findContentModelByWpPath($path): ?AssetsModelInterface
    {
        /** @var \BetaKiller\Repository\RepositoryHasWordpressPathInterface[] $repositories */
        $repositories = [
            $this->imageRepo,
            $this->thumbRepo,
            $this->attachmentRepo,
        ];

        foreach ($repositories as $repo) {
            $model = $repo->findByWpPath($path);

            if (!$model) {
                continue;
            }

            if (!($model instanceof AssetsModelInterface)) {
                throw new AssetsException('Model :name must be instance of :must', [
                    ':name' => \get_class($model),
                    ':must' => AssetsModelInterface::class,
                ]);
            }

            return $model;
        }

        return null;
    }
}
