<?php
namespace BetaKiller\Helper;

use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\AttachmentAssetsProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Factory\RepositoryFactory;
use BetaKiller\Model\ContentPost;
use BetaKiller\Repository\ContentAttachmentRepository;
use BetaKiller\Repository\ContentCategoryRepository;
use BetaKiller\Repository\ContentCommentRepository;
use BetaKiller\Repository\ContentCommentStatusRepository;
use BetaKiller\Repository\ContentImageRepository;
use BetaKiller\Repository\ContentPostRepository;
use BetaKiller\Repository\ContentPostThumbnailRepository;
use BetaKiller\Repository\ContentYoutubeRecordRepository;

class ContentHelper
{
    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    private $shortcodeFacade;

    /**
     * @var \BetaKiller\Factory\RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var \BetaKiller\Assets\AssetsProviderFactory
     */
    private $providerFactory;

    public function __construct(
        RepositoryFactory $repositoryFactory,
        AssetsProviderFactory $providerFactory,
        ShortcodeFacade $shortcodeFacade
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->providerFactory   = $providerFactory;
        $this->shortcodeFacade   = $shortcodeFacade;
    }

    public function getPostRepository(): ContentPostRepository
    {
        return $this->repositoryFactory->create('ContentPost');
    }

    public function getCommentRepository(): ContentCommentRepository
    {
        return $this->repositoryFactory->create('ContentComment');
    }

    public function getCommentStatusRepository(): ContentCommentStatusRepository
    {
        return $this->repositoryFactory->create('ContentCommentStatus');
    }

    public function getCategoryRepository(): ContentCategoryRepository
    {
        return $this->repositoryFactory->create('ContentCategory');
    }

    public function getImageRepository(): ContentImageRepository
    {
        return $this->repositoryFactory->create('ContentImage');
    }

    public function getAttachmentRepository(): ContentAttachmentRepository
    {
        return $this->repositoryFactory->create('ContentAttachment');
    }

    public function getPostThumbnailRepository(): ContentPostThumbnailRepository
    {
        return $this->repositoryFactory->create('ContentPostThumbnail');
    }

    public function getYoutubeRecordRepository(): ContentYoutubeRecordRepository
    {
        return $this->repositoryFactory->create('ContentYoutubeRecord');
    }

    public function getPostContentPreview(ContentPost $post, ?int $length = null, ?string $end_chars = null): string
    {
        $text = $post->getContent();
        $text = strip_tags($text);
        $text = $this->shortcodeFacade->stripTags($text);

        return \Text::limit_chars($text, $length ?? 250, $end_chars ?? '...', true);
    }

    public function getPostThumbnailAssetsProvider(): ImageAssetsProviderInterface
    {
        return $this->providerFactory->createFromModelCodename('ContentPostThumbnail');
    }

    public function getImageAssetsProvider(): ImageAssetsProviderInterface
    {
        return $this->providerFactory->createFromModelCodename('ContentImage');
    }

    public function getAttachmentAssetsProvider(): AttachmentAssetsProviderInterface
    {
        return $this->providerFactory->createFromModelCodename('ContentAttachment');
    }

    /**
     * Detect and instantiate assets provider by file MIME-type
     *
     * @param string $mimeType
     *
     * @return \BetaKiller\Assets\Provider\AssetsProviderInterface
     * @deprecated
     */
    public function createAssetsProviderFromMimeType(string $mimeType): AssetsProviderInterface
    {
        /** @var \BetaKiller\Assets\Provider\AssetsProviderInterface[] $mimeProviders */
        $mimeProviders = [
            $this->getImageAssetsProvider(),
        ];

        foreach ($mimeProviders as $provider) {
            $allowedMimes = $provider->getAllowedMimeTypes();

            if ($allowedMimes && \is_array($allowedMimes) && \in_array($mimeType, $allowedMimes, true)) {
                return $provider;
            }
        }

        // Default way
        return $this->getAttachmentAssetsProvider();
    }
}
