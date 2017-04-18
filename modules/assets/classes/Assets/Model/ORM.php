<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Model\UserInterface;

/**
 * Class Assets_Model_ORM
 *
 * Abstract class for all ORM-based asset models
 */
abstract class Assets_Model_ORM extends ORM implements Assets_ModelInterface {

    protected function _initialize()
    {
        $this->belongs_to(array(
            'uploaded_by_user'  =>  array(
                'model'         =>  'User',
                'foreign_key'   =>  'uploaded_by',
            )
        ));

        parent::_initialize();
    }

    /**
     * Returns original file name (user-defined filename of uploaded file)
     *
     * @return string
     */
    public function get_original_name()
    {
        return $this->get('original_name');
    }

    /**
     * Stores original file name (user-defined filename of uploaded file)
     *
     * @param $name
     * @return $this
     */
    public function set_original_name($name)
    {
        $this->set('original_name', $name);
        return $this->make_hash();
    }

    /**
     * Returns file`s hash string
     *
     * @return string
     */
    public function get_hash()
    {
        return $this->get('hash');
    }

    /**
     * Creates unique hash from original filename and stores it in `hash` property
     * @return $this
     */
    protected function make_hash()
    {
        $hash = $this->get_hash() ?: md5(microtime().$this->get_original_name());
        return $this->set('hash', $hash);
    }

    /**
     * Returns MIME-type of the file
     *
     * @return string
     */
    public function get_mime()
    {
        return $this->get('mime');
    }

    /**
     * Sets MIME-type of the file
     *
     * @param string $mime
     * @return $this
     */
    public function set_mime($mime)
    {
        return $this->set('mime', $mime);
    }

    /**
     * Returns User model, who uploaded the file
     *
     * @return UserInterface
     */
    public function get_uploaded_by()
    {
        return $this->get('uploaded_by_user');
    }

    /**
     * Sets user, who uploaded the file
     *
     * @param UserInterface $user
     * @return $this
     */
    public function set_uploaded_by(UserInterface $user)
    {
        return $this->set('uploaded_by_user', $user);
    }

    /**
     * Returns the date and time when asset was uploaded
     *
     * @return DateTime|NULL
     */
    public function get_uploaded_at()
    {
        return $this->get_datetime_column_value('uploaded_at');
    }

    /**
     * Sets the date and time when asset was uploaded
     *
     * @param \DateTime $time
     *
     * @return $this
     */
    public function set_uploaded_at(DateTime $time)
    {
        return $this->set_datetime_column_value('uploaded_at', $time);
    }

    /**
     * Returns the date and time when asset was modified
     *
     * @return DateTime|NULL
     */
    public function get_last_modified_at()
    {
        return $this->get_datetime_column_value('last_modified_at');
    }

    /**
     * Sets the date and time when asset was modified
     *
     * @param \DateTime $time
     *
     * @return $this
     */
    public function set_last_modified_at(DateTime $time)
    {
        return $this->set_datetime_column_value('last_modified_at', $time);
    }

    /**
     * Returns file size in bytes
     *
     * @return integer
     */
    public function get_size()
    {
        return $this->get('size');
    }

    /**
     * Stores file size in bytes
     *
     * @param integer $size
     * @return $this
     */
    public function set_size($size)
    {
        return $this->set('size', $size);
    }

    /**
     * Returns path for file in storage
     *
     * @return string
     */
    public function get_storage_file_name()
    {
        return $this->get_ab_path();
    }

    /**
     * Returns file model url (for deploy url and deploy path)
     *
     * @return string
     */
    public function get_url()
    {
        return $this->get_ab_path('/');
    }

    /**
     * Returns deep path for current model (f0/a4/f0a435a89cc65a93d341)
     *
     * @param string $delimiter
     * @return string
     */
    protected function get_ab_path($delimiter = DIRECTORY_SEPARATOR)
    {
        $hash = $this->get_hash();
        $depth = $this->get_ab_depth();
        $length = $this->get_ab_part_length();

        $parts = array();

        for ( $i = 0; $i < $depth; $i++ )
        {
            $parts[] = substr($hash, $i*$length, $length);
        }

        $parts[] = $hash;

        return implode($delimiter, $parts);
    }

    /**
     * How many layers in path
     *
     * @return int
     */
    protected function get_ab_depth()
    {
        return 2;
    }

    /**
     * How many letters are in path part
     *
     * @return int
     */
    protected function get_ab_part_length()
    {
        return 2;
    }

    /**
     * Performs file model search by hash
     *
     * @param string $url
     * @return Assets_ModelInterface|NULL
     */
    public function by_url($url)
    {
        $hash = basename($url);

        /** @var Assets_Model_ORM $model */
        $model = $this->model_factory()->where('hash', '=', $hash)->find();

        return $model->loaded() ? $model : NULL;
    }

    /**
     * Returns URL for uploading new assets
     *
     * @return string
     */
    public function get_upload_url()
    {
        return $this->get_provider()->get_upload_url();
    }

    /**
     * Returns URL to original file/image
     *
     * @return null|string
     */
    public function get_original_url()
    {
        return $this->loaded()
            ? $this->get_provider()->get_original_url($this)
            : NULL;
    }

    public function delete()
    {
        // Removing file from storage
        $this->get_provider()->delete($this);

        return parent::delete();
    }

    /**
     * Returns array representation of the model
     *
     * @return array
     */
    public function to_json()
    {
        return $this->as_array();
    }

    /**
     * Returns assets provider associated with current model
     *
     * @return Assets_Provider|Assets_Provider_Image
     */
    abstract protected function get_provider();

}
