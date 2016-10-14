<?php

class Model_AdminImageFile extends Model_AdminContentFile
{
    protected function get_file_table_name()
    {
        return 'admin_content_images';
    }

    /**
     * Returns assets provider associated with current model
     *
     * @return Assets_Provider_AdminImage
     */
    protected function get_provider()
    {
        return Assets_Provider_Factory::instance()->create('AdminImage');
    }

    public function get_img_tag_arguments_with_srcset(array $attributes = [])
    {
        $attributes = array_merge([
            'src'       =>  $this->get_original_url(),
            'alt'       =>  $this->get_alt(),
            'title'     =>  $this->get_title(),
            'srcset'    =>  $this->get_srcset(),
        ], $attributes);

        return $attributes;
    }

    public function get_srcset()
    {
        $sizes = $this->get_provider()->get_allowed_preview_sizes();
        $srcset = [];

        if ($sizes)
        {
            foreach ($sizes as $size)
            {
                $width = intval($size);
                $srcset[] = $this->get_preview_url($size).' '.$width.'w';
            }
        }

        return implode(', ', $srcset);
    }

//    /**
//     * Rule definitions for validation
//     *
//     * @return array
//     */
//    public function rules()
//    {
//        return parent::rules() + [
//            'alt'   =>  [
//                ['not_empty']
//            ],
//        ];
//    }

//    /**
//     * @param int $value
//     * @return $this|ORM
//     * @throws Kohana_Exception
//     */
//    public function set_width($value)
//    {
//        return $this->set('width', (int) $value);
//    }
//
//    /**
//     * @return string
//     * @throws Kohana_Exception
//     */
//    public function get_width()
//    {
//        return $this->get('width');
//    }
//
//    /**
//     * @param int $value
//     * @return $this|ORM
//     * @throws Kohana_Exception
//     */
//    public function set_height($value)
//    {
//        return $this->set('height', (int) $value);
//    }
//
//    /**
//     * @return string
//     * @throws Kohana_Exception
//     */
//    public function get_height()
//    {
//        return $this->get('height');
//    }

    /**
     * @param string $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_alt($value)
    {
        return $this->set('alt', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_alt()
    {
        return $this->get('alt');
    }

    /**
     * @param string $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_title($value)
    {
        return $this->set('title', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_title()
    {
        return $this->get('title');
    }

    public function set_wp_path($value)
    {
        return $this->set('wp_path', $value);
    }

    public function get_wp_path()
    {
        return $this->get('wp_path');
    }

//    public function mark_is_moved()
//    {
//        return $this->set('is_moved', TRUE);
//    }
//
//    public function is_moved()
//    {
//        return (bool) $this->get('is_moved');
//    }

//    /**
//     * @param $path
//     * @return Model_AdminImageFile|NULL
//     * @throws Kohana_Exception
//     */
//    public function find_moved_from_path($path)
//    {
//        $moved = $this
//            ->model_factory()
//            ->filter_wp_path($path)
//            ->filter_is_moved()
//            ->find();
//
//        return $moved->loaded() ? $moved : NULL;
//    }

    /**
     * @param $value
     * @return Model_AdminImageFile
     */
    public function filter_wp_path($value)
    {
        return $this->where('wp_path', '=', $value);
    }

//    /**
//     * @param bool $value
//     * @return Model_AdminImageFile
//     */
//    public function filter_is_moved($value = TRUE)
//    {
//        return $this->where('is_moved', '=', (bool) $value);
//    }
}
