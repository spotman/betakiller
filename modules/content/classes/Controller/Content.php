<?php

use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Exception\PermanentRedirectHttpException;

class Controller_Content extends Controller
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentImageRepository
     */
    private $imageRepo;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentPostThumbnailRepository
     */
    private $thumbRepo;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentAttachmentRepository
     */
    private $attachmentRepo;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function action_files_bc_redirect()
    {
        $file = $this->request->param('file');

        if (!$file) {
            throw new NotFoundHttpException();
        }

        $path = '/'.ltrim($this->request->uri(), '/');

        $model = $this->findContentModelByWpPath($path);

        if (!$model) {
            throw new NotFoundHttpException();
        }

        $url = $this->assetsHelper->getOriginalUrl($model);

        throw new PermanentRedirectHttpException($url);
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
