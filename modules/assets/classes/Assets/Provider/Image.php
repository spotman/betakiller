<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Assets_Provider_Image
 * Abstract class for all image assets
 */
abstract class Assets_Provider_Image extends Assets_Provider {

    /**
     * @param Assets_Model $model
     * @return string
     * @throws HTTP_Exception_501
     */
    public function get_preview_url(Assets_Model $model)
    {
        return $this->_get_item_url('preview', $model);
    }

    public function prepare_preview(Assets_Model $model)
    {
        $content = $this->get_content($model);

        return $this->resize(
            $content,
            $this->get_preview_max_width(),
            $this->get_preview_max_height(),
            $this->get_preview_quality()
        );
    }

    protected function _upload($model, $content)
    {
        return $this->resize(
            $content,
            $this->get_upload_max_width(),
            $this->get_upload_max_height()
        );
    }

    protected function resize($original_content, $width, $height, $quality = 100)
    {
        // Creating temporary file
        $temp_file_name = tempnam(sys_get_temp_dir(), 'image-resize');

        if ( ! $temp_file_name )
            throw new Assets_Provider_Exception('Can not create temporary file for image resizing');

        // Putting content into it
        file_put_contents($temp_file_name, $original_content);

        // Creating resizing instance
        $image = Image::factory($temp_file_name);

        $resized_content = $image
            ->resize($width, $height)
            ->render(NULL /* auto */, $quality);

        // Deleting temp file
        unlink($temp_file_name);

        return $resized_content;
    }

    /**
     * @return int
     */
    abstract public function get_upload_max_height();

    /**
     * @return int
     */
    abstract public function get_upload_max_width();

    /**
     * @return int
     */
    abstract public function get_preview_max_height();

    /**
     * @return int
     */
    abstract public function get_preview_max_width();

    /**
     * @return int
     */
    abstract public function get_preview_quality();
}