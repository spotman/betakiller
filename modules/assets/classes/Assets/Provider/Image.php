<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Assets_Provider_Image
 * Abstract class for all image assets
 */
abstract class Assets_Provider_Image extends Assets_Provider {

    // Dimensions values delimiter
    const SIZE_DELIMITER = 'x';

    const SIZE_ORIGINAL = 'original';
    const SIZE_PREVIEW = 'preview';

    /**
     * @param Assets_ModelInterface $model
     * @param string $size    300x200
     * @return string
     * @throws Assets_Provider_Exception
     */
    public function get_preview_url(Assets_ModelInterface $model, $size = NULL)
    {
        $url = $model->get_url();

        if ( ! $url )
            throw new Assets_Provider_Exception('Model must have url');

        $size = $this->determine_size($size);

        $options = array(
            'provider'  =>  $this->_codename,
            'action'    =>  'preview',
            'item_url'  =>  $url,
            'size'      =>  $size,
            'ext'       =>  $this->get_model_extension($model),
        );

        return Route::url('assets-provider-item-preview', $options);
    }

    public function make_size_string($width = NULL, $height = NULL)
    {
        return $width.self::SIZE_DELIMITER.$height;
    }

    /**
     * @param string $size
     *
     * @return int[]
     */
    protected function parse_size_dimensions($size)
    {
        $dimensions = explode(self::SIZE_DELIMITER, $size);
        $width = $dimensions[0] ? (int) $dimensions[0] : NULL;
        $height = $dimensions[1] ? (int) $dimensions[1] : NULL;

        return $this->pack_dimensions($width, $height);
    }

    protected function pack_dimensions($width, $height)
    {
        return [$width, $height];
    }

    protected function determine_size($size)
    {
        $allowed_sizes = $this->get_allowed_preview_sizes();

        if ( $size === NULL && count($allowed_sizes) > 0 )
        {
            $size = $allowed_sizes[0];
        }

        if (!$size)
            throw new Assets_Provider_Exception('Can not determine image size for :provider', [':provider' => $this->_codename]);

        return $size;
    }

    protected function calculate_dimensions_ratio($width, $height)
    {
        if (!$height || !$width) {
            throw new Assets_Provider_Exception('Can not calculate ratio for incomplete dimensions :size', [
                ':size' => $this->make_size_string($width, $height)
            ]);
        }

        return $width / $height;
    }

    protected function restore_omitted_dimensions($width, $height, $original_ratio)
    {
        // Fill omitted dimensions
        if (!$width) {
            $width = $height * $original_ratio;
        } elseif (!$height) {
            $height = $width / $original_ratio;
        }

        return $this->pack_dimensions($width, $height);
    }

    public function make_preview(Assets_Model_ImageInterface $model, $size)
    {
        $this->check_preview_size($size);

        $content = $this->get_content($model);

        $dimensions = $this->parse_size_dimensions($size);

        $width = $dimensions[0];
        $height = $dimensions[1];

        if ( !$width AND !$height )
            throw new Assets_Provider_Exception('Preview size must have width or height defined');

        return $this->resize(
            $content,
            $width,
            $height,
            $this->get_preview_quality()
        );
    }

    protected function check_preview_size($size)
    {
        $allowed_sizes = $this->get_allowed_preview_sizes();

        if ( ! $allowed_sizes OR ! in_array($size, $allowed_sizes) )
            throw new Assets_Provider_Exception('Preview size :size is not allowed', [':size' => $size]);
    }

    /**
     * @param Assets_Model_ImageInterface $model
     * @param string $content
     * @param array $_post_data
     * @param string $file_path
     * @return string
     * @throws Assets_Provider_Exception
     */
    protected function _upload($model, $content, array $_post_data, $file_path)
    {
        $this->preset_width_and_height($model, $file_path);

        $max_width = $this->get_upload_max_width();
        $max_height = $this->get_upload_max_height();

        if (!$max_width || !$max_height)
            throw new Assets_Provider_Exception('Upload max dimensions must be set for provider :name', [':name' => $this->_codename]);

        // Skip resizing if image is fitting requirements
        if ($model->get_width() <= $max_width && $model->get_height() <= $max_height)
            return $content;

        return $this->resize(
            $content,
            $max_width,
            $max_height
        );
    }

    /**
     * Detects image dimensions from provided file
     *
     * @param Assets_Model_ImageInterface $model
     * @param string $file_path
     * @throws Assets_Provider_Exception
     */
    protected function preset_width_and_height(Assets_Model_ImageInterface $model, $file_path)
    {
        try
        {
            // Creating image instance
            $image = Image::factory($file_path);

            $model
                ->set_width($image->width)
                ->set_height($image->height);
        }
        catch ( Exception $e )
        {
            throw new Assets_Provider_Exception('Can not detect image width and height: :message', [':message' => $e->getMessage()]);
        }
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
        $temp_file_name = tempnam(sys_get_temp_dir(), 'image-resize-temp');

        if ( ! $temp_file_name )
            throw new Assets_Provider_Exception('Can not create temporary file for image resizing');

        try {
            // Putting content into it
            file_put_contents($temp_file_name, $original_content);

            // Creating resizing instance
            $image = Image::factory($temp_file_name);

            // Detect original dimensions and ratio
            $original_width = $image->width;
            $original_height = $image->height;
            $original_ratio = $this->calculate_dimensions_ratio($original_width, $original_height);

            $dimensions = $this->restore_omitted_dimensions($width, $height, $original_ratio);
            $width = $dimensions[0];
            $height = $dimensions[1];

            $resize_ratio = $this->calculate_dimensions_ratio($width, $height);

            if ($original_ratio == $resize_ratio) {
                $image->resize($width, $height);
            } else {

                $image->resize($width, $height, Image::INVERSE)->crop($width, $height);
            }

            $resized_content = $image->render(NULL /* auto */, $quality);
        } catch ( Exception $e ) {
            throw new Assets_Provider_Exception('Can not resize image, reason: :message', [':message' => $e->getMessage()]);
        }

        // Deleting temp file
        unlink($temp_file_name);

        return $resized_content;
    }

    protected function _get_item_deploy_filename(Request $request)
    {
        $size = $request->param('size');
        return $request->action().( $size ? '-'.$size : '').'.'.$request->param('ext');
    }

    public function get_arguments_for_img_tag(Assets_Model_ImageInterface $model, $size, array $attributes = [])
    {
        $original_url = $this->get_original_url($model);

        if ($size == self::SIZE_ORIGINAL)
        {
            $src = $original_url;
            $width = $model->get_width();
            $height = $model->get_height();
        }
        else
        {
            $size = ($size != self::SIZE_PREVIEW) ? $size : null;

            $size = $this->determine_size($size);
            $dimensions = $this->parse_size_dimensions($size);

            $dimensions = $this->restore_omitted_dimensions($dimensions[0], $dimensions[1], $this->get_model_ratio($model));
            $width = $dimensions[0];
            $height = $dimensions[1];

            $src = $this->get_preview_url($model, $size);
        }

        // TODO recalculate dimensions if $attributes['width'] or 'height' exists

        $image_ratio = $this->calculate_dimensions_ratio($width, $height);

        $attributes = array_merge([
            'src'               =>  $src,
            'width'             =>  $width,
            'height'            =>  $height,
            'srcset'            =>  $this->get_srcset_attribute_value($model, $image_ratio),
            'data-original-url' =>  $original_url,
            'data-id'           =>  $model->get_id(),
        ], $attributes);

        return $attributes;
    }

    protected function get_srcset_attribute_value(Assets_Model_ImageInterface $model, $ratio = NULL)
    {
        $model_ratio = $this->get_model_ratio($model);

        if (!$ratio) {
            $ratio = $model_ratio;
        }

        $sizes = $this->get_srcset_sizes($ratio);
        $srcset = [];

        if ($sizes)
        {
            foreach ($sizes as $size)
            {
                $width = intval($size);
                $url = $this->get_preview_url($model, $size);
                $srcset[] = $this->make_srcset_width_option($url, $width);
            }
        }

        // If original image ratio is allowed
        if ($model_ratio == $ratio) {
            // Add srcset for original image
            $url = $this->get_original_url($model);
            $srcset[] = $this->make_srcset_width_option($url, $model->get_width());
        }

        return implode(', ', array_filter($srcset));
    }

    protected function get_srcset_sizes($ratio = NULL)
    {
        $allowed_sizes = $this->get_allowed_preview_sizes();

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
        $dimensions = $this->parse_size_dimensions($size);

        if ($original_ratio) {
            $dimensions = $this->restore_omitted_dimensions($dimensions[0], $dimensions[1], $original_ratio);
        }

        return $this->calculate_dimensions_ratio($dimensions[0], $dimensions[1]);
    }

    protected function get_model_ratio(Assets_Model_ImageInterface $image)
    {
        return $this->calculate_dimensions_ratio($image->get_width(), $image->get_height());
    }

    protected function make_srcset_width_option($url, $width)
    {
        return $url.' '.$width.'w';
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
     * @return int
     */
    public function get_preview_quality()
    {
        // This is optimal for JPEG
        return 80;
    }
}
