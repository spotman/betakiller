<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\Model\ContentPost;
use BetaKiller\Url\BeforeProcessingInterface;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostItemIFace extends AbstractAppBase implements BeforeProcessingInterface
{
    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\AssetsHelper $assetsHelper
     */
    public function __construct(AssetsHelper $assetsHelper)
    {
        $this->assetsHelper = $assetsHelper;
    }

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Kohana_Exception
     */
    public function beforeProcessing(ServerRequestInterface $request): void
    {
        // Count guest views only
        if (ServerRequestHelper::isGuest($request)) {
            $model = ContentUrlContainerHelper::getContentPost($request);
            $model->incrementViewsCount()->save();
        }
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $model = ContentUrlContainerHelper::getContentPost($request);
        $stack = ServerRequestHelper::getUrlElementStack($request);

        $previewMode = UrlElementHelper::isCurrentZone(ZoneInterface::PREVIEW, $stack);

        if ($previewMode) {
            // See latest revision data
            $model->useLatestRevision();
        }

        return [
            'post'        => $this->getPostData($model),
            'previewMode' => $previewMode,
        ];
    }

    protected function getPostData(ContentPost $model): array
    {
        $this->setLastModified($model->getApiLastModified());
        $this->setExpiresInterval(new \DateInterval('P1D')); // One day

        $thumbnails = [];

        foreach ($model->getThumbnails() as $thumb) {
            $thumbnails[] = $this->assetsHelper->getAttributesForImgTag($thumb, $thumb::SIZE_ORIGINAL);

            // Get image last modified and set it to iface
            $this->setLastModified($thumb->getLastModifiedAt());
        }

        return [
            'id'         => $model->getID(),
            'label'      => $model->getLabel(),
            'content'    => $model->getContent(),
            'created_at' => $model->getCreatedAt(),
            'updated_at' => $model->getUpdatedAt(),
            'thumbnails' => $thumbnails,
            'is_page'    => $model->isPage(),
            'is_default' => $model->isDefault(),
        ];
    }
}
