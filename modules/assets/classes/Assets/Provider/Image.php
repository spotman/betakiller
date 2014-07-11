<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Assets_Provider_Image
 * Abstract class for all image assets
 */
abstract class Assets_Provider_Image extends Assets_Provider {

    /**
     * @param Assets_Model $model
     * @param string $size    300x200
     * @return string
     * @throws Assets_Provider_Exception
     */
    public function get_preview_url(Assets_Model $model, $size)
    {
        $url = $model->get_url();

        if ( ! $url )
            throw new Assets_Provider_Exception('Model must have url');

        $options = array(
            'provider'  =>  $this->_codename,
            'action'    =>  'preview',
            'item_url'  =>  $url,
            'size'      =>  $size,
        );

        return Route::url('assets-provider-item-preview', $options);
    }

    public function prepare_preview(Assets_Model $model, $size)
    {
        $content = $this->get_content($model);

        $dimensions = explode('x', $size);
        $width = $dimensions[0] ?: NULL;
        $height = $dimensions[1] ?: NULL;

        if ( ! $width AND ! $height )
            throw new Assets_Provider_Exception('Preview size must have width or height defined');

        return $this->resize(
            $content,
            $width,
            $height,
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
     * Defines allowed sizes for previews
     * Returns array of strings like this
     *
     * array('300x200', '75x75')
     *
     * @return array
     */
    abstract public function get_allowed_preview_sizes();

    /**
     * @return int
     */
    abstract public function get_preview_quality();
}