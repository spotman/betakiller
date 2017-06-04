<?php

use BetaKiller\Assets\Model\AssetsModelInterface;

interface AssetsModelImageInterface extends AssetsModelInterface
{
    /**
     * @return int
     */
    public function get_id();

    /**
     * @param string|null $size
     * @return string|null
     */
    public function get_preview_url($size = NULL);

    /**
     * @return int
     */
    public function get_upload_max_width();

    /**
     * @return int
     */
    public function get_upload_max_height();

    /**
     * @return int
     */
    public function get_width();

    /**
     * @return int
     */
    public function get_height();

    /**
     * @param int $value
     * @return $this
     */
    public function set_width($value);

    /**
     * @param int $value
     * @return $this
     */
    public function set_height($value);
}
