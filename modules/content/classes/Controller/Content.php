<?php

use BetaKiller\Assets\AssetsException;
use BetaKiller\Assets\Model\AssetsModelInterface;

class Controller_Content extends Controller
{
    use \BetaKiller\Helper\ContentTrait;

    /**
     * @var \BetaKiller\Helper\ContentHelper
     * @Inject
     */
    private $contentHelper;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    public function action_files_bc_redirect()
    {
        $file = $this->param('file');

        if (!$file) {
            throw new HTTP_Exception_404();
        }

        $path = '/'.ltrim($this->getRequest()->uri(), '/');

        $model = $this->find_content_model_by_wp_path($path);

        if (!$model) {
            throw new HTTP_Exception_404();
        }

        $url = $this->assetsHelper->getOriginalUrl($model);

        $this->redirect($url, 301);
    }

    /**
     * @param string $path
     *
     * @return AssetsModelInterface|null
     */
    protected function find_content_model_by_wp_path($path): ?AssetsModelInterface
    {
        /** @var \BetaKiller\Content\RepositoryHasWordpressPathInterface[] $repositories */

        $repositories = [
            $this->contentHelper->getImageRepository(),
            $this->contentHelper->getPostThumbnailRepository(),
            $this->contentHelper->getAttachmentRepository(),
        ];

        foreach ($repositories as $repo) {
            $model = $repo->find_by_wp_path($path);

            if (!$model) {
                continue;
            }

            if (!($model instanceof AssetsModelInterface)) {
                throw new AssetsException('Model :name must be instance of :must', [
                    ':name' => get_class($model),
                    ':must' => AssetsModelInterface::class,
                ]);
            }

            return $model;
        }

        return null;
    }
}
