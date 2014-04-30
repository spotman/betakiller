<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Assets_Model_ORM
 *
 * Abstract class for all ORM-based asset models
 */
abstract class Assets_Model_ORM extends ORM implements Assets_Model {

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
        return $this->set('original_name', $name);
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
        $hash = md5(microtime() . $this->get_original_name());
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
     * @return Model_User
     */
    public function get_uploaded_by()
    {
        return $this->get('uploaded_by');
    }

    /**
     * Sets user, who uploaded the file
     *
     * @param Model_User $user
     * @return $this
     */
    public function set_uploaded_by(Model_User $user)
    {
        return $this->set('uploaded_by', $user);
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
        $hash = $this->get_hash();
        return Text::limit_chars($hash, 2).DIRECTORY_SEPARATOR.$hash;
    }

    /**
     * Returns file model url (for deploy url and deploy path)
     *
     * @return string
     */
    public function get_url()
    {
        return $this->get_storage_file_name();
    }

    /**
     * Performs file model search by hash
     *
     * @param string $url
     * @return Assets_Model|NULL
     */
    public function by_url($url)
    {
        $hash = basename($url);
        $model = ORM::factory($this->object_name())->where('hash', '=', $hash)->find();
        return $model->loaded() ? $model : NULL;
    }

    public function create(Validation $validation = NULL)
    {
        $this->make_hash();
        return parent::create($validation);
    }

}