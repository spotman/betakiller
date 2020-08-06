<?php
declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Model\HasPreviewAssetsModelInterface;

final class ImageProcessor
{
    /**
     * @param $width
     * @param $height
     *
     * @return float
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public static function calculateDimensionsRatio(int $width, int $height): float
    {
        if (!$height || !$width) {
            throw new AssetsException('Can not calculate ratio for incomplete dimensions :size', [
                ':size' => self::makeSizeString($width, $height),
            ]);
        }

        return $width / $height;
    }

    /**
     * @param int|null $width
     * @param int|null $height
     * @param float    $originalRatio
     *
     * @return int[]|null[]
     */
    public static function restoreOmittedDimensions(?int $width, ?int $height, float $originalRatio): array
    {
        // Fill omitted dimensions
        if (!$width) {
            $width = (int)($height * $originalRatio);
        } elseif (!$height) {
            $height = (int)($width / $originalRatio);
        }

        return self::packDimensions($width, $height);
    }

    /**
     * @param string $size
     *
     * @return int[]
     */
    public static function parseSizeDimensions(string $size): array
    {
        $dimensions = explode(HasPreviewAssetsModelInterface::SIZE_DELIMITER, $size);
        $width      = $dimensions[0] ? (int)$dimensions[0] : null;
        $height     = $dimensions[1] ? (int)$dimensions[1] : null;

        return self::packDimensions($width, $height);
    }

    /**
     * @param string       $originalContent
     * @param integer|null $width
     * @param integer|null $height
     * @param integer      $quality
     * @param bool         $isPreview
     *
     * @param bool|null    $crop
     *
     * @return string Processed content
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public static function resize(
        string $originalContent,
        ?int $width,
        ?int $height,
        int $quality,
        bool $isPreview,
        bool $crop = null
    ): string {
        try {
            $image = \Image::fromContent($originalContent);

            // Detect original dimensions and ratio
            $originalWidth  = $image->width;
            $originalHeight = $image->height;
            $originalRatio  = self::calculateDimensionsRatio($originalWidth, $originalHeight);

            [$width, $height] = self::restoreOmittedDimensions($width, $height, $originalRatio);

            $resizeRatio = self::calculateDimensionsRatio($width, $height);

            if ($originalRatio === $resizeRatio) {
                $image->resize($width, $height);
            } elseif ($isPreview && $crop) {
                $image
                    ->resize($width, $height, \Image::INVERSE)
                    ->crop($width, $height);
            } else {
                $image
                    ->resize($width, $height, \Image::AUTO);
            }

            return $image->render(null /* auto */, $quality);
        } catch (\Throwable $e) {
            throw new AssetsException('Can not resize image, reason: :message', [
                ':message' => $e->getMessage(),
            ]);
        }
    }

    private static function packDimensions(?int $width, ?int $height): array
    {
        return [$width, $height];
    }

    private static function makeSizeString(int $width = null, int $height = null): string
    {
        return $width.AssetsModelImageInterface::SIZE_DELIMITER.$height;
    }
}
