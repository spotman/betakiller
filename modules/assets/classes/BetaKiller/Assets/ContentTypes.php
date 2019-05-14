<?php
declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Assets\Exception\AssetsException;
use function finfo_file;
use function finfo_open;
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
        $builder->add('text/rtf', 'rtf');

        return new MimeTypes($builder->getMapping());
    }

    /**
     * Detect mime type from file content (secure, can be used for uploaded files)
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileMimeType(string $path): string
    {
        $fileResource = finfo_open(FILEINFO_MIME_TYPE);

        return finfo_file($fileResource, $path);
    }

    /**
     * Get mime-type from file extension (insecure, do not use it on uploaded files)
     *
     * @param string $ext
     *
     * @return string
     */
    public function getExtensionMimeType(string $ext): string
    {
        return $this->mimey->getMimeType($ext);
    }

    /**
     * @param string $mimeType
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
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
     * @param array $mimeTypes
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function getTypesExtensions(array $mimeTypes): array
    {
        $extensions = [];

        foreach ($mimeTypes as $mimeType) {
            $extensions[] = $this->getExtensions($mimeType);
        }

        return array_unique(array_merge(...$extensions));
    }

    /**
     * @param string $mimeType
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function getPrimaryExtension(string $mimeType): string
    {
        $extensions = $this->getExtensions($mimeType);

        return $extensions[0];
    }
}
