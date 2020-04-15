<?php
namespace BetaKiller\Helper;

use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\AttachmentAssetsProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Model\ContentPost;

class ContentHelper
{
    /**
     * Simplified role for moderators
     */
    public const ROLE_CONTENT_MODERATOR = 'content-moderator';
    /**
     * Role for writers
     */
    public const ROLE_WRITER = 'writer';

    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    private $shortcodeFacade;

    /**
     * @var \BetaKiller\Assets\AssetsProviderFactory
     */
    private $providerFactory;

    public function __construct(
        AssetsProviderFactory $providerFactory,
        ShortcodeFacade $shortcodeFacade
    ) {
        $this->providerFactory   = $providerFactory;
        $this->shortcodeFacade   = $shortcodeFacade;
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
        return $this->providerFactory->createFromCodename('ContentPostThumbnail');
    }

    public function getImageAssetsProvider(): ImageAssetsProviderInterface
    {
        return $this->providerFactory->createFromCodename('ContentImage');
    }

    public function getAttachmentAssetsProvider(): AttachmentAssetsProviderInterface
    {
        return $this->providerFactory->createFromCodename('ContentAttachment');
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
