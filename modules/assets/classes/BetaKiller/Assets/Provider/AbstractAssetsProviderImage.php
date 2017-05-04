<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\AssetsProviderException;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use Exception;
use Image;
use Request;
use Route;

/**
 * Class AbstractAssetsProviderImage
 * Abstract class for all image assets
 */
abstract class AbstractAssetsProviderImage extends AbstractAssetsProvider
{

    // Dimensions values delimiter
    const SIZE_DELIMITER = 'x';

    const SIZE_ORIGINAL = 'original';
    const SIZE_PREVIEW  = 'preview';

    /**
     * @param AssetsModelInterface $model
     * @param string               $size 300x200
     *
     * @return string
     * @throws AssetsProviderException
     */
    public function getPreviewUrl(AssetsModelInterface $model, $size = null)
    {
        $url = $model->getUrl();

        if (!$url) {
            throw new AssetsProviderException('Model must have url', [
                ':name' => $model->getStorageFileName()
            ]);
        }

        $size = $this->determineSize($size);

        $options = [
            'provider' => $this->getUrlKey(),
            'action'   => 'preview',
            'item_url' => $url,
            'size'     => $size,
            'ext'      => $this->getModelExtension($model),
        ];

        // TODO Remove Route dependency
        return Route::url('assets-provider-item-preview', $options);
    }

    private function makeSizeString($width = null, $height = null)
    {
        return $width.self::SIZE_DELIMITER.$height;
    }

    /**
     * @param string $size
     *
     * @return int[]
     */
    private function parseSizeDimensions($size)
    {
        $dimensions = explode(self::SIZE_DELIMITER, $size);
        $width      = $dimensions[0] ? (int)$dimensions[0] : null;
        $height     = $dimensions[1] ? (int)$dimensions[1] : null;

        return $this->packDimensions($width, $height);
    }

    private function packDimensions($width, $height)
    {
        return [$width, $height];
    }

    private function determineSize($size)
    {
        $allowed_sizes = $this->getAllowedPreviewSizes();

        if (!$size && count($allowed_sizes) > 0) {
            $size = $allowed_sizes[0];
        }

        if (!$size) {
            throw new AssetsProviderException('Can not determine image size for :provider', [
                ':provider' => $this->codename,
            ]);
        }

        return $size;
    }

    /**
     * @param $width
     * @param $height
     *
     * @return float
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    private function calculateDimensionsRatio($width, $height)
    {
        if (!$height || !$width) {
            throw new AssetsProviderException('Can not calculate ratio for incomplete dimensions :size', [
                ':size' => $this->makeSizeString($width, $height),
            ]);
        }

        return $width / $height;
    }

    private function restoreOmittedDimensions($width, $height, $original_ratio)
    {
        // Fill omitted dimensions
        if (!$width) {
            $width = $height * $original_ratio;
        } elseif (!$height) {
            $height = $width / $original_ratio;
        }

        return $this->packDimensions($width, $height);
    }

    public function makePreview(AssetsModelImageInterface $model, $size)
    {
        $this->check_preview_size($size);

        $content = $this->getContent($model);

        list($width, $height) = $this->parseSizeDimensions($size);

        if (!$width && !$height) {
            throw new AssetsProviderException('Preview size must have width or height defined');
        }

        return $this->resize(
            $content,
            $width,
            $height,
            $this->get_preview_quality()
        );
    }

    protected function check_preview_size($size)
    {
        $allowedSizes = $this->getAllowedPreviewSizes();

        if (!$allowedSizes || !in_array($size, $allowedSizes, true)) {
            throw new AssetsProviderException('Preview size :size is not allowed', [':size' => $size]);
        }
    }

    /**
     * @param AssetsModelImageInterface $model
     * @param string                    $content
     * @param array                     $_post_data
     * @param string                    $file_path
     *
     * @return string
     * @throws AssetsProviderException
     */
    protected function customUploadProcessing($model, $content, array $_post_data, $file_path)
    {
        $max_width  = $this->getUploadMaxWidth();
        $max_height = $this->getUploadMaxHeight();

        if (!$max_width || !$max_height) {
            throw new AssetsProviderException('Upload max dimensions must be set for provider :name', [
                ':name' => $this->codename,
            ]);
        }

        // Skip resizing if image is fitting requirements
        if ($model->getWidth() <= $max_width && $model->getHeight() <= $max_height) {
            return $content;
        }

        return $this->resize(
            $content,
            $max_width,
            $max_height
        );
    }

    /**
     * After upload processing
     *
     * @param AssetsModelImageInterface $model
     * @param array                     $_post_data
     */
    protected function postUploadProcessing($model, array $_post_data)
    {
        $this->presetWidthAndHeight($model);

        // Save dimensions
        $model->save();

        parent::postUploadProcessing($model, $_post_data);
    }

    /**
     * Detects image dimensions from provided file
     *
     * @param AssetsModelImageInterface $model
     *
     * @throws AssetsProviderException
     */
    protected function presetWidthAndHeight(AssetsModelImageInterface $model)
    {
        $content = $this->getContent($model);

        $info = Image::from_content($content);

        $model
            ->setWidth($info->width)
            ->setHeight($info->height);
    }

    /**
     * @param     $originalContent
     * @param     $width
     * @param     $height
     * @param int $quality
     *
     * @returns string Processed content
     * @throws AssetsProviderException
     */
    protected function resize($originalContent, $width, $height, $quality = 100)
    {
        $image = Image::from_content($originalContent);

        try {
            // Detect original dimensions and ratio
            $originalWidth  = $image->width;
            $originalHeight = $image->height;
            $originalRatio  = $this->calculateDimensionsRatio($originalWidth, $originalHeight);

            list($width, $height) = $this->restoreOmittedDimensions($width, $height, $originalRatio);

            $resize_ratio = $this->calculateDimensionsRatio($width, $height);

            if ($originalRatio === $resize_ratio) {
                $image->resize($width, $height);
            } else {
                $image->resize($width, $height, Image::INVERSE)->crop($width, $height);
            }

            return $image->render(null /* auto */, $quality);
        } catch (Exception $e) {
            throw new AssetsProviderException('Can not resize image, reason: :message', [
                ':message' => $e->getMessage(),
            ]);
        }
    }

    protected function getItemDeployFilename(Request $request)
    {
        $size = $request->param('size');

        return $request->action().($size ? '-'.$size : '').'.'.$request->param('ext');
    }

    public function getArgumentsForImgTag(AssetsModelImageInterface $model, $size, array $attributes = [])
    {
        $original_url = $this->getOriginalUrl($model);

        if ($size === self::SIZE_ORIGINAL) {
            $src    = $original_url;
            $width  = $model->getWidth();
            $height = $model->getHeight();
        } else {
            $size = ($size !== self::SIZE_PREVIEW) ? $size : null;

            $size       = $this->determineSize($size);
            $dimensions = $this->parseSizeDimensions($size);

            list($width, $height) = $this->restoreOmittedDimensions($dimensions[0], $dimensions[1], $this->getModelRatio($model));

            $src = $this->getPreviewUrl($model, $size);
        }

        // TODO recalculate dimensions if $attributes['width'] or 'height' exists

        $image_ratio = $this->calculateDimensionsRatio($width, $height);

        $attributes = array_merge([
            'src'               => $src,
            'width'             => $width,
            'height'            => $height,
            'srcset'            => $this->getSrcsetAttributeValue($model, $image_ratio),
            'data-original-url' => $original_url,
            'data-id'           => $model->get_id(),
        ], $attributes);

        return $attributes;
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelImageInterface $model
     * @param float|null                                         $ratio
     *
     * @return string
     */
    protected function getSrcsetAttributeValue(AssetsModelImageInterface $model, $ratio = null)
    {
        $modelRatio = $this->getModelRatio($model);

        if (!$ratio) {
            $ratio = $modelRatio;
        }

        $sizes  = $this->get_srcset_sizes($ratio);
        $srcset = [];

        if ($sizes) {
            foreach ($sizes as $size) {
                $width    = intval($size);
                $url      = $this->getPreviewUrl($model, $size);
                $srcset[] = $this->make_srcset_width_option($url, $width);
            }
        }

        // If original image ratio is allowed
        if ($modelRatio == $ratio) {
            // Add srcset for original image
            $url      = $this->getOriginalUrl($model);
            $srcset[] = $this->make_srcset_width_option($url, $model->getWidth());
        }

        return implode(', ', array_filter($srcset));
    }

    protected function get_srcset_sizes($ratio = null)
    {
        $allowed_sizes = $this->getAllowedPreviewSizes();

        // Return all sizes if no ratio filter was set
        if (!$ratio) {
            return $allowed_sizes;
        }

        $sizes = [];

        // Filtering sizes by ratio
        foreach ($allowed_sizes as $size) {
            $size_ratio = $this->get_size_ratio($size, $ratio);

            // Skip sizes with another ratio
            if ($ratio != $size_ratio) {
                continue;
            }

            $sizes[] = $size;
        }

        return $sizes;
    }

    protected function get_size_ratio($size, $original_ratio = null)
    {
        $dimensions = $this->parseSizeDimensions($size);

        if ($original_ratio) {
            $dimensions = $this->restoreOmittedDimensions($dimensions[0], $dimensions[1], $original_ratio);
        }

        return $this->calculateDimensionsRatio($dimensions[0], $dimensions[1]);
    }

    protected function getModelRatio(AssetsModelImageInterface $image)
    {
        return $this->calculateDimensionsRatio($image->getWidth(), $image->getHeight());
    }

    protected function make_srcset_width_option($url, $width)
    {
        return $url.' '.$width.'w';
    }

    /**
     * @return int
     */
    abstract public function getUploadMaxHeight();

    /**
     * @return int
     */
    abstract public function getUploadMaxWidth();

    /**
     * Defines allowed sizes for previews
     * Returns array of strings like this
     *
     * array('300x200', '75x75', '400x', 'x250')
     *
     * @return array
     */
    abstract public function getAllowedPreviewSizes();

    /**
     * @return int
     */
    public function get_preview_quality()
    {
        // This is optimal for JPEG
        return 80;
    }
}
