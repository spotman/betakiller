<?php
namespace BetaKiller\Assets\Model;

use BetaKiller\Assets\Provider\AbstractAssetsProviderImage;

abstract class AbstractAssetsOrmImageModel extends AbstractAssetsOrmModel implements AssetsModelImageInterface
{
    const SIZE_ORIGINAL = AbstractAssetsProviderImage::SIZE_ORIGINAL;
    const SIZE_PREVIEW  = AbstractAssetsProviderImage::SIZE_PREVIEW;

    public function getPreviewUrl($size = null)
    {
        return $this->loaded()
            ? $this->getProvider()->getPreviewUrl($this, $size)
            : null;
    }

    public function getUploadMaxWidth()
    {
        return $this->getProvider()->getUploadMaxWidth();
    }

    public function getUploadMaxHeight()
    {
        return $this->getProvider()->getUploadMaxHeight();
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->get('width');
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->get('height');
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setWidth($value)
    {
        return $this->set('width', (int)$value);
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setHeight($value)
    {
        return $this->set('height', (int)$value);
    }

    public function getAttributesForImgTag($size, array $attributes = [])
    {
        return $this->getProvider()->getArgumentsForImgTag($this, $size, $attributes);
    }
}
