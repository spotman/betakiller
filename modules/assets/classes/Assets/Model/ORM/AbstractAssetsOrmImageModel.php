<?php use BetaKiller\Assets\Model\AbstractAssetsOrmModel;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Provider\AbstractAssetsProviderImage;


abstract class AbstractAssetsOrmImageModel extends AbstractAssetsOrmModel implements AssetsModelImageInterface
{
    const SIZE_ORIGINAL = AbstractAssetsProviderImage::SIZE_ORIGINAL;
    const SIZE_PREVIEW = AbstractAssetsProviderImage::SIZE_PREVIEW;

    public function getPreviewUrl($size = NULL)
    {
        return $this->loaded()
            ? $this->getProvider()->get_preview_url($this, $size)
            : NULL;
    }

    public function getUploadMaxWidth()
    {
        return $this->getProvider()->get_upload_max_width();
    }

    public function getUploadMaxHeight()
    {
        return $this->getProvider()->get_upload_max_height();
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
     * @return $this
     */
    public function setWidth($value)
    {
        return $this->set('width', (int) $value);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setHeight($value)
    {
        return $this->set('height', (int) $value);
    }

    public function get_attributes_for_img_tag($size, array $attributes = [])
    {
        return $this->getProvider()->get_arguments_for_img_tag($this, $size, $attributes);
    }
}
