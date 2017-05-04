<?php use BetaKiller\Assets\AssetsException;
use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Provider\AbstractAssetsProvider;
use BetaKiller\Assets\Provider\AbstractAssetsProviderImage;

class Controller_Assets extends Controller
{
    const ACTION_ORIGINAL = 'original';
    const ACTION_PREVIEW  = 'preview';
    const ACTION_DELETE   = 'delete';
    const ACTION_CROP     = 'crop'; // Kept for BC

    /**
     * @var AbstractAssetsProvider
     */
    protected $provider;

    /**
     * Common action for uploading files through provider
     *
     * @throws AssetsException
     */
    public function action_upload()
    {
        // This method responds via JSON (all exceptions will be caught automatically)
        $this->content_type_json();

        // Restrict multiple files at once
        if (count($_FILES) > 1) {
            throw new AssetsException('Only one file can be uploaded at once');
        }

        $this->provider_factory();

        // Getting first uploaded file
        $_file = array_shift($_FILES);

        // Getting additional POST data
        $_post_data = $this->request->post();

        // Uploading via provider
        $model = $this->provider->upload($_file, $_post_data);

        // Returns
        $this->send_json(self::JSON_SUCCESS, $model->toJson());
    }

    public function action_original()
    {
        $this->provider_factory();

        $model = $this->fromItemDeployUrl();

        $this->checkExtension($model);

        // Get file content
        $content = $this->provider->getContent($model);

        // Deploy to cache
        $this->deploy($model, $content);

        // Send last modified date
        $this->response->last_modified($model->getLastModifiedAt());

        // Send file content + headers
        $this->send_file($content, $model->getMime());
    }

    public function action_preview()
    {
        $this->provider_factory();

        if (!($this->provider instanceof AbstractAssetsProviderImage)) {
            throw new AssetsException('Preview can be served only by instances of :must', [
                ':must' => AbstractAssetsProviderImage::class,
            ]);
        }

        $size  = $this->getSizeParam();
        $model = $this->fromItemDeployUrl();

        $this->checkExtension($model);

        $previewContent = $this->provider->makePreview($model, $size);

        // Deploy to cache
        $this->deploy($model, $previewContent);

        // Send last modified date
        $this->response->last_modified($model->getLastModifiedAt());

        // Send file content + headers
        $this->send_file($previewContent, $model->getMime());
    }

    public function action_crop()
    {
        $this->provider_factory();

        if (!($this->provider instanceof AbstractAssetsProviderImage)) {
            throw new AssetsException('Cropping can be processed only by instances of :must', [
                ':must' => AbstractAssetsProviderImage::class,
            ]);
        }

        $size  = $this->getSizeParam();
        $model = $this->fromItemDeployUrl();

        $this->checkExtension($model);

        $preview_url = $model->getPreviewUrl($size);

        // Redirect for SEO backward compatibility
        $this->response->redirect($preview_url, 301);
    }

    private function getSizeParam()
    {
        return $this->param('size');
    }

    public function action_delete()
    {
        // This method responds via JSON (all exceptions will be caught automatically)
        $this->content_type_json();

        $this->provider_factory();

        // Get file model by hash value
        $model = $this->fromItemDeployUrl();

        // Delete file through provider
        $model->delete();

        $this->send_json(self::JSON_SUCCESS);
    }

    protected function provider_factory()
    {
        $requestKey = $this->param('provider');

        if (!$requestKey) {
            throw new AssetsException('You must specify provider codename');
        }

        $this->provider = AssetsProviderFactory::instance()->createFromUrlKey($requestKey);
        $providerKey = $this->provider->getUrlKey();

        if ($requestKey !== $providerKey) {
            // Redirect to canonical url
            $model = $this->fromItemDeployUrl();

            $this->redirectToCanonicalUrl($model);
        }
    }

    /**
     * @return \BetaKiller\Assets\Model\AssetsModelInterface|\BetaKiller\Assets\Model\AssetsModelImageInterface|NULL
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \HTTP_Exception_404
     */
    protected function fromItemDeployUrl()
    {
        $url = $this->param('item_url');

        if (!$url) {
            throw new AssetsException('You must specify item url');
        }

        try {
            // Find asset model by url
            $model = $this->provider->getModelByDeployUrl($url);
        } catch (AssetsException $e) {
            // File not found
            throw new HTTP_Exception_404;
        }

        return $model;
    }

    protected function deploy(AssetsModelInterface $model, $content)
    {
        $this->provider->deploy($this->request, $model, $content);
    }

    protected function checkExtension(AssetsModelInterface $model)
    {
        $requestExt = $this->request->param('ext');
        $modelExt   = $this->provider->getModelExtension($model);

        if (!$requestExt || $requestExt !== $modelExt) {
            $this->redirectToCanonicalUrl($model);
        }
    }

    private function redirectToCanonicalUrl(AssetsModelInterface $model)
    {
        $url = $this->getCanonicalUrl($model);

        $this->response->redirect($url, 302);
    }

    private function getCanonicalUrl(AssetsModelInterface $model)
    {
        $action = $this->request->action();

        switch ($action) {
            case self::ACTION_ORIGINAL:
                return $this->provider->getOriginalUrl($model);

            case 'preview':
            case 'crop':
                /** @var AbstractAssetsProviderImage $provider */
                $provider = $this->provider;

                return $provider->getPreviewUrl($model, $this->getSizeParam());

            case 'delete':
                return $this->provider->getDeleteUrl($model);

            default:
                throw new HTTP_Exception_400('Unknown action :value', [':value' => $action]);
        }
    }
}
