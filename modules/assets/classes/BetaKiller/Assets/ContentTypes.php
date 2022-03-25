<?php
declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Assets\Exception\AssetsException;
use Mimey\MimeMappingBuilder;
use Mimey\MimeTypes;
use Mimey\MimeTypesInterface;
use function finfo_file;
use function finfo_open;

class ContentTypes
{
    /**
     * @var \Mimey\MimeTypesInterface
     */
    private MimeTypesInterface $mimeTypes;

    /**
     * ContentTypes constructor.
     */
    public function __construct()
    {
        $this->mimeTypes = $this->buildMimeTypes();
    }

    private function buildMimeTypes(): MimeTypesInterface
    {
        // Create a builder using the built-in conversions as the basis.
        $builder = MimeMappingBuilder::create();

        // Add a conversion. This conversion will take precedence over existing ones.
        $builder->add('image/jpeg', 'jpg');
        $builder->add('text/rtf', 'rtf');
        $builder->add('text/plain', 'map');

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
        $mimeType = $this->mimeTypes->getMimeType($ext);

        if (!$mimeType) {
            throw new AssetsException('MIME can not be detected for ":ext" extension', [
                ':ext' => $ext
            ]);
        }

        return $mimeType;
    }

    /**
     * @param string $mimeType
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function getExtensions(string $mimeType): array
    {
        $extensions = $this->mimeTypes->getAllExtensions($mimeType);

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

        $extensions = array_unique(array_merge(...$extensions));

        sort($extensions);

        return $extensions;
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
