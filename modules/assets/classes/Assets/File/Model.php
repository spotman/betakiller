<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Interface Assets_File_Model
 *
 * Abstract model interface for asset file
 */
interface Assets_File_Model {

    /**
     * Returns path for file in storage
     *
     * @return string
     */
    public function get_full_path();

    /**
     * Returns User model, who uploaded the file
     *
     * @return Model_User
     */
    public function get_uploaded_by();

    /**
     * Sets user, who uploaded the file
     *
     * @param Model_User $user
     * @return $this
     */
    public function set_uploaded_by(Model_User $user);

    /**
     * Returns file`s hash string
     *
     * @return string
     */
    public function get_hash();

    /**
     * Creates unique hash from original filename and stores it in `hash` property
     * @return $this
     */
    public function make_hash();

    /**
     * Returns original file name (user-defined filename of uploaded file)
     *
     * @return string
     */
    public function get_original_name();

    /**
     * Stores original file name (user-defined filename of uploaded file)
     *
     * @param $name
     * @return $this
     */
    public function set_original_name($name);

    /**
     * Returns MIME-type of the file
     *
     * @return string
     */
    public function get_mime();

    /**
     * Sets MIME-type of the file
     *
     * @param string $mime
     * @return $this
     */
    public function set_mime($mime);

    /**
     * Returns file size in bytes
     *
     * @return integer
     */
    public function get_size();
    /**
     * Stores file size in bytes
     *
     * @param integer $size
     * @return $this
     */
    public function set_size($size);

    /**
     * Saves the model info
     *
     * @return bool
     */
    public function save();

    /**
     * Removes model
     *
     * @return bool
     */
    public function delete();

    /**
     * Returns array representation of the model
     *
     * @return array
     */
    public function to_json();

    /**
     * Performs file model search by hash
     *
     * @param string $hash
     * @return Assets_File_Model|NULL
     */
    public function by_hash($hash);

}
