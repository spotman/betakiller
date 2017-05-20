<?php
namespace BetaKiller\Assets\Model;

interface AssetsModelImageInterface extends AssetsModelInterface
{
    const SIZE_ORIGINAL  = 'original';
    const SIZE_PREVIEW   = 'preview';

    // Dimensions values delimiter
    const SIZE_DELIMITER = 'x';

    /**
     * @return int
     */
    public function get_id();

    /**
     * @param string|null $size
     *
     * @return string|null
     */
    public function getPreviewUrl($size = null);

    /**
     * @return int
     */
    public function getUploadMaxWidth();

    /**
     * @return int
     */
    public function getUploadMaxHeight();

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @return int
     */
    public function getHeight();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setWidth($value);

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setHeight($value);
}
