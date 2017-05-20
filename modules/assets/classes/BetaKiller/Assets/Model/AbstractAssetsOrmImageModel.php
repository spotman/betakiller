<?php
namespace BetaKiller\Assets\Model;

abstract class AbstractAssetsOrmImageModel extends AbstractAssetsOrmModel implements AssetsModelImageInterface
{
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
