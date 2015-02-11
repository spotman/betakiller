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
    public function get_preview_url(Assets_Model $model, $size = NULL)
    {
        $url = $model->get_url();

        if ( ! $url )
            throw new Assets_Provider_Exception('Model must have url');

        $allowed_sizes = $this->get_allowed_preview_sizes();

        if ( $size === NULL AND count($allowed_sizes) == 1 )
        {
            $size = $allowed_sizes[0];
        }

        $options = array(
            'provider'  =>  $this->_codename,
            'action'    =>  'preview',
            'item_url'  =>  $url,
            'size'      =>  $size,
        );

        return Route::url('assets-provider-item-preview', $options);
    }

    /**
     * @param Assets_Model $model
     * @param string $size    300x200
     * @return string
     * @throws Assets_Provider_Exception
     */
    public function get_crop_url(Assets_Model $model, $size = NULL)
    {
        $url = $model->get_url();

        if ( ! $url )
            throw new Assets_Provider_Exception('Model must have url');

        $allowed_sizes = $this->get_allowed_crop_sizes();

        if ( $size === NULL AND count($allowed_sizes) == 1 )
        {
            $size = $allowed_sizes[0];
        }

        $options = array(
            'provider'  =>  $this->_codename,
            'action'    =>  'crop',
            'item_url'  =>  $url,
            'size'      =>  $size,
        );

        return Route::url('assets-provider-item-crop', $options);
    }

    public function prepare_preview(Assets_Model $model, $size)
    {
        $this->check_preview_size($size);

        $content = $this->get_content($model);

        $dimensions = explode('x', $size);
        $width = $dimensions[0] ? (int) $dimensions[0] : NULL;
        $height = $dimensions[1] ? (int) $dimensions[1] : NULL;

        if ( ! $width AND ! $height )
            throw new Assets_Provider_Exception('Preview size must have width or height defined');

        return $this->resize(
            $content,
            $width,
            $height,
            $this->get_preview_quality()
        );
    }

    public function crop(Assets_Model $model, $size)
    {
        $this->check_crop_size($size);

        $content = $this->get_content($model);

        $dimensions = explode('x', $size);
        $width = $dimensions[0] ? (int) $dimensions[0] : NULL;
        $height = $dimensions[1] ? (int) $dimensions[1] : NULL;

        if ( ! $width AND $height )
            $width = $height;

        if ( ! $height AND $width )
            $height = $width;

        if ( ! $width OR ! $height )
            throw new Assets_Provider_Exception('Crop size must have width AND height defined');

        return $this->_crop_resize_and_center($content, $width, $height, $this->get_preview_quality());
    }

    /**
     * @param string $original_content
     * @param int $width
     * @param int $height
     * @param int $quality
     * @returns string Cropped image content
     * @throws Assets_Provider_Exception
     */
    protected function _crop_resize_and_center($original_content, $width, $height, $quality)
    {
        if ( ! $original_content )
            throw new Assets_Provider_Exception('No content for resizing');

        // Creating temporary file
        $temp_file_name = tempnam(sys_get_temp_dir(), 'image-crop');

        if ( ! $temp_file_name )
            throw new Assets_Provider_Exception('Can not create temporary file for image cropping');

        try
        {
            // Putting content into it
            file_put_contents($temp_file_name, $original_content);

            // Creating resizing instance
            $image = Image::factory($temp_file_name);

            $cropped_content = $image
                ->resize($width, $height, Image::INVERSE)
                ->crop($width, $height)
                ->render(NULL /* auto */, $quality);

            // Deleting temp file
            unlink($temp_file_name);

            return $cropped_content;
        }
        catch ( Exception $e )
        {
            throw new Assets_Provider_Exception('Can not crop image');
        }
    }

    public function check_preview_size($size)
    {
        $allowed_sizes = $this->get_allowed_preview_sizes();

        if ( ! $allowed_sizes OR ! in_array($size, $allowed_sizes) )
            throw new Assets_Provider_Exception('Preview size :size is not allowed', [':size' => $size]);
    }

    public function check_crop_size($size)
    {
        $allowed_sizes = $this->get_allowed_crop_sizes();

        if ( ! $allowed_sizes OR ! in_array($size, $allowed_sizes) )
            throw new Assets_Provider_Exception('Crop size :size is not allowed', [':size' => $size]);
    }

    protected function _upload($model, $content, array $_post_data)
    {
        return $this->resize(
            $content,
            $this->get_upload_max_width(),
            $this->get_upload_max_height()
        );
    }

    /**
     * @param $original_content
     * @param $width
     * @param $height
     * @param int $quality
     * @returns string Processed content
     * @throws Assets_Provider_Exception
     */
    protected function resize($original_content, $width, $height, $quality = 100)
    {
        if ( ! $original_content )
            throw new Assets_Provider_Exception('No content for resizing');

        // Creating temporary file
        $temp_file_name = tempnam(sys_get_temp_dir(), 'image-resize');

        if ( ! $temp_file_name )
            throw new Assets_Provider_Exception('Can not create temporary file for image resizing');

        try
        {
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
        catch ( Exception $e )
        {
            throw new Assets_Provider_Exception('Can not resize image');
        }
    }

    protected function _get_item_deploy_filename(Request $request)
    {
        $size = $request->param('size');
        return parent::_get_item_deploy_filename($request).( $size ? '-'.$size : '');
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
     * array('300x200', '75x75', '400x', 'x250')
     *
     * @return array
     */
    abstract public function get_allowed_preview_sizes();

    /**
     * Defines allowed sizes for cropping
     * Returns array of strings like this
     *
     * array('300x200', '75x75')
     *
     * @return array|NULL
     */
    abstract public function get_allowed_crop_sizes();

    /**
     * @return int
     */
    public function get_preview_quality()
    {
        // This is optimal for JPEG
        return 80;
    }
}
