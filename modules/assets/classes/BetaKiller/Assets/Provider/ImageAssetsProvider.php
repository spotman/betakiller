<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\Exception\AssetsProviderException;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Model\HasPreviewAssetsModelInterface;
use BetaKiller\Model\UserInterface;
use Image;
use Throwable;

/**
 * Class ImageAssetsProvider
 * Common class for all image assets
 */
final class ImageAssetsProvider extends AbstractHasPreviewAssetsProvider implements ImageAssetsProviderInterface
{
    public const CONFIG_MODEL_MAX_HEIGHT_KEY = 'max-height';
    public const CONFIG_MODEL_MAX_WIDTH_KEY  = 'max-width';

    /**
     * Returns array of allowed actions` names
     *
     * @return string[]
     */
    public function getActions(): array
    {
        return [
            self::ACTION_PREVIEW,
            self::ACTION_ORIGINAL,
            self::ACTION_UPLOAD,
            self::ACTION_DOWNLOAD,
            self::ACTION_DELETE,
        ];
    }

    private function makeSizeString(int $width = null, int $height = null): string
    {
        return $width.AssetsModelImageInterface::SIZE_DELIMITER.$height;
    }

    /**
     * @param string $size
     *
     * @return int[]
     */
    private function parseSizeDimensions(string $size): array
    {
        $dimensions = explode(AssetsModelImageInterface::SIZE_DELIMITER, $size);
        $width      = $dimensions[0] ? (int)$dimensions[0] : null;
        $height     = $dimensions[1] ? (int)$dimensions[1] : null;

        return $this->packDimensions($width, $height);
    }

    private function packDimensions(?int $width, ?int $height): array
    {
        return [$width, $height];
    }

    /**
     * @param $width
     * @param $height
     *
     * @return float
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    private function calculateDimensionsRatio(int $width, int $height): float
    {
        if (!$height || !$width) {
            throw new AssetsProviderException('Can not calculate ratio for incomplete dimensions :size', [
                ':size' => $this->makeSizeString($width, $height),
            ]);
        }

        return $width / $height;
    }

    private function restoreOmittedDimensions(?int $width, ?int $height, float $originalRatio): array
    {
        // Fill omitted dimensions
        if (!$width) {
            $width = (int)($height * $originalRatio);
        } elseif (!$height) {
            $height = (int)($width / $originalRatio);
        }

        return $this->packDimensions($width, $height);
    }

    /**
     * @param \BetaKiller\Assets\Model\HasPreviewAssetsModelInterface $model
     * @param string                                                  $size
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function makePreviewContent(HasPreviewAssetsModelInterface $model, string $size): string
    {
        $size = $this->determinePreviewSize($size);

        $content = $this->getContent($model);

        [$width, $height] = $this->parseSizeDimensions($size);

        if (!$width && !$height) {
            throw new AssetsProviderException('Preview size must have width or height defined');
        }

        return $this->resize(
            $content,
            $width,
            $height,
            $this->getPreviewQuality()
        );
    }

    /**
     * @param string                    $content
     * @param AssetsModelImageInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @internal param array $_post_data
     *
     */
    protected function customContentProcessing(string $content, $model): string
    {
        $maxWidth  = $this->getUploadMaxWidth();
        $maxHeight = $this->getUploadMaxHeight();

        if (!$maxWidth || !$maxHeight) {
            throw new AssetsProviderException('Upload max dimensions must be set for provider :name', [
                ':name' => $this->codename,
            ]);
        }

        // Skip resizing if image is fitting requirements
        if ($model->getWidth() <= $maxWidth && $model->getHeight() <= $maxHeight) {
            return $content;
        }

        return $this->resize(
            $content,
            $maxWidth,
            $maxHeight,
            100 // 100% quality for original image
        );
    }

    /**
     * @param string  $originalContent
     * @param integer $width
     * @param integer $height
     * @param integer $quality
     *
     * @returns string Processed content
     * @return string
     * @throws AssetsProviderException
     */
    private function resize(string $originalContent, ?int $width, ?int $height, int $quality): string
    {
        try {
            $image = Image::fromContent($originalContent);

            // Detect original dimensions and ratio
            $originalWidth  = $image->width;
            $originalHeight = $image->height;
            $originalRatio  = $this->calculateDimensionsRatio($originalWidth, $originalHeight);

            [$width, $height] = $this->restoreOmittedDimensions($width, $height, $originalRatio);

            $resizeRatio = $this->calculateDimensionsRatio($width, $height);

            if ($originalRatio === $resizeRatio) {
                $image->resize($width, $height);
            } elseif ($this->isCroppedPreview()) {
                $image->resize($width, $height, Image::INVERSE)->crop($width, $height);
            } else {
                $image->resize($width, $height, Image::AUTO);
            }

            return $image->render(null /* auto */, $quality);
        } catch (Throwable $e) {
            throw new AssetsProviderException('Can not resize image, reason: :message', [
                ':message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelImageInterface $model
     * @param null|string                                        $size
     * @param array|null                                         $attrs
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    public function getAttributesForImgTag(
        AssetsModelImageInterface $model,
        ?string $size = null,
        array $attrs = null
    ): array {
        $attrs = $attrs ?? [];

        if ($size === AssetsModelImageInterface::SIZE_ORIGINAL) {
            $src    = $this->getOriginalUrl($model);
            $width  = $model->getWidth();
            $height = $model->getHeight();
        } else {
            $size = ($size !== AssetsModelImageInterface::SIZE_PREVIEW) ? $size : null;

            $size       = $this->determinePreviewSize($size);
            $dimensions = $this->parseSizeDimensions($size);
            $modelRatio = $this->getModelRatio($model);

            [$width, $height] = $this->restoreOmittedDimensions($dimensions[0], $dimensions[1], $modelRatio);

            $src = $this->getPreviewUrl($model, $size);
        }

        $targetRatio = $this->calculateDimensionsRatio($width, $height);

        $targetWidth  = $attrs['width'] ?? null;
        $targetHeight = $attrs['height'] ?? null;

        // Recalculate dimensions if $attributes['width'] or 'height' exists
        if ($targetWidth || $targetHeight) {
            [$width, $height] = $this->restoreOmittedDimensions($targetWidth, $targetHeight, $targetRatio);
        }

        $attrs = array_merge([
            'src'     => $src,
            'alt'     => $model->getAlt(),
            'srcset'  => $this->getSrcsetAttributeValue($model, $targetRatio),
            'data-id' => $model->getID(),
        ], $attrs, [
            'width'  => $width,
            'height' => $height,
        ]);

        return $attrs;
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelImageInterface $model
     * @param float|null                                         $ratio
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    private function getSrcsetAttributeValue(AssetsModelImageInterface $model, float $ratio = null): string
    {
        $originalWidth = $model->getWidth();
        $modelRatio    = $this->getModelRatio($model);

        if (!$ratio) {
            $ratio = $modelRatio;
        }

        $sizes  = $this->getSrcsetSizes($ratio);
        $srcset = [];

        if ($sizes) {
            foreach ($sizes as $size) {
                $width = (int)$size;

                // Skip sizes which are larger than original image
                if ($width + 100 <= $originalWidth) {
                    $url      = $this->getPreviewUrl($model, $size);
                    $srcset[] = $this->makeSrcsetWidthOption($url, $width);
                }
            }
        }

        // If original image ratio is allowed
        if ($modelRatio === $ratio) {
            // Add srcset for original image
            $url      = $this->getOriginalUrl($model);
            $srcset[] = $this->makeSrcsetWidthOption($url, $model->getWidth());
        }

        return implode(', ', array_filter($srcset));
    }

    /**
     * @param float|null $ratio
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    private function getSrcsetSizes(float $ratio = null): array
    {
        $allowedSizes = $this->getAllowedPreviewSizes();

        // Return all sizes if no ratio filter was set
        if (!$ratio) {
            return $allowedSizes;
        }

        $sizes = [];

        // Filtering sizes by ratio
        foreach ($allowedSizes as $size) {
            $sizeRatio = $this->getSizeRatio($size, $ratio);

            // Skip sizes with another ratio
            if ($ratio !== $sizeRatio) {
                continue;
            }

            $sizes[] = $size;
        }

        return $sizes;
    }

    /**
     * @param string     $size
     * @param float|null $originalRatio
     *
     * @return float
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    private function getSizeRatio(string $size, float $originalRatio = null): float
    {
        $dimensions = $this->parseSizeDimensions($size);

        if ($originalRatio) {
            $dimensions = $this->restoreOmittedDimensions($dimensions[0], $dimensions[1], $originalRatio);
        }

        return $this->calculateDimensionsRatio($dimensions[0], $dimensions[1]);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelImageInterface $image
     *
     * @return float
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    private function getModelRatio(AssetsModelImageInterface $image): float
    {
        return $this->calculateDimensionsRatio($image->getWidth(), $image->getHeight());
    }

    private function makeSrcsetWidthOption(string $url, int $width): string
    {
        return $url.' '.$width.'w';
    }

    /**
     * @return int
     */
    public function getUploadMaxHeight(): ?int
    {
        return $this->config->getProviderConfigValue($this, [
            self::CONFIG_MODEL_UPLOAD_KEY,
            self::CONFIG_MODEL_MAX_HEIGHT_KEY,
        ]);
    }

    /**
     * @return int
     */
    public function getUploadMaxWidth(): ?int
    {
        return $this->config->getProviderConfigValue($this, [
            self::CONFIG_MODEL_UPLOAD_KEY,
            self::CONFIG_MODEL_MAX_WIDTH_KEY,
        ]);
    }

    /**
     * @param string                          $fullPath
     * @param string                          $originalName
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsUploadException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function store(string $fullPath, string $originalName, UserInterface $user): AssetsModelInterface
    {
        /** @var \BetaKiller\Assets\Model\AssetsModelImageInterface $model */
        $model = parent::store($fullPath, $originalName, $user);

        $this->detectImageDimensions($model);

        return $model;
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelImageInterface $model
     *
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    private function detectImageDimensions(AssetsModelImageInterface $model): void
    {
        $content = $this->getContent($model);

        $info = Image::fromContent($content);

        $model->setWidth($info->width);
        $model->setHeight($info->height);
    }
}
