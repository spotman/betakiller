<?php
declare(strict_types=1);

namespace BetaKiller\Assets;


use BetaKiller\Exception;
use Mimey\MimeMappingBuilder;
use Mimey\MimeTypes;

class ContentTypes
{
    /**
     * @var MimeTypes
     */
    private $mimey;

    /**
     * ContentTypes constructor.
     */
    public function __construct()
    {
        $this->mimey = $this->buildMimey();
    }

    private function buildMimey(): MimeTypes
    {
        // Create a builder using the built-in conversions as the basis.
        $builder = MimeMappingBuilder::create();

        // Add a conversion. This conversion will take precedence over existing ones.
        $builder->add('image/jpeg', 'jpg');

        return new MimeTypes($builder->getMapping());
    }

    /**
     * Detect mime type from file content
     *
     * @param string $content
     *
     * @return string
     */
    public function getMimeTypeFromContent(string $content): string
    {
        $fileInfo = new \finfo(FILEINFO_MIME);

        return $fileInfo->buffer($content);
    }

    /**
     * @param string $mimeType
     *
     * @return array
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function getExtensions(string $mimeType): array
    {
        $extensions = $this->mimey->getAllExtensions($mimeType);

        if (!$extensions) {
            throw new AssetsException('MIME :mime has no defined extension', [':mime' => $mimeType]);
        }

        return array_unique($extensions);
    }

    /**
     * @param string $mimeType
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function getPrimaryExtension(string $mimeType): string
    {
        $extensions = $this->getExtensions($mimeType);

        return $extensions[0];
    }
}
