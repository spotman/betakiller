<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostItem extends AbstractAppBase
{
    /**
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
     */
    protected $urlParametersHelper;

    /**
     * @var \BetaKiller\Model\ContentPost
     */
    private $contentModel;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * @Inject
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $ifaceHelper;

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\AssetsHelper              $assetsHelper
     * @param \BetaKiller\Helper\ContentUrlContainerHelper $urlParametersHelper
     * @param \BetaKiller\Model\UserInterface              $user
     */
    public function __construct(
        AssetsHelper $assetsHelper,
        ContentUrlContainerHelper $urlParametersHelper,
        UserInterface $user
    ) {
        $this->urlParametersHelper = $urlParametersHelper;
        $this->user                = $user;
        $this->assetsHelper        = $assetsHelper;
    }

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before(): void
    {
        // Count guest views only
        if ($this->user->isGuest()) {
            $this->getContentModel()->incrementViewsCount()->save();
        }
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $model = $this->getContentModel();

        $previewMode = $this->ifaceHelper->isCurrentZone(ZoneInterface::PREVIEW);

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

    /**
     * @return \DateInterval
     * @throws \Exception
     */
    public function getDefaultExpiresInterval(): \DateInterval
    {
        return new \DateInterval('P1D'); // One day
    }

    /**
     * @return \BetaKiller\Model\ContentPost
     */
    protected function detectContentModel(): ContentPost
    {
        return $this->urlParametersHelper->getContentPost();
    }

    /**
     * @return \BetaKiller\Model\ContentPost
     */
    protected function getContentModel(): ContentPost
    {
        if (!$this->contentModel) {
            $this->contentModel = $this->detectContentModel();
        }

        return $this->contentModel;
    }
}
