<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Interface Assets_ModelInterface
 *
 * Abstract model interface for asset file
 */
interface Assets_ModelInterface {

    /**
     * Returns filename for storage
     *
     * @return string
     */
    public function get_storage_file_name();

    /**
     * Returns file model url (for deploy url and deploy path)
     *
     * @return string
     */
    public function get_url();

    /**
     * Performs file model search by url (deploy url dispatching)
     *
     * @param string $url
     * @return Assets_ModelInterface|NULL
     */
    public function by_url($url);

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
     * Returns the date and time when asset was uploaded
     *
     * @return DateTime
     */
    public function get_uploaded_at();

    /**
     * Sets the date and time when asset was uploaded
     *
     * @param \DateTime $time
     *
     * @return mixed
     */
    public function set_uploaded_at(DateTime $time);

    /**
     * Returns the date and time when asset was last modified
     *
     * @return DateTime
     */
    public function get_last_modified_at();

    /**
     * Sets the date and time when asset was last modified
     *
     * @param \DateTime $time
     *
     * @return mixed
     */
    public function set_last_modified_at(DateTime $time);

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
     * Returns TRUE if model is found and loaded
     *
     * @return bool
     */
    public function loaded();

    /**
     * Returns URL for uploading new assets
     *
     * @return string
     */
    public function get_upload_url();

    /**
     * Returns URL to original file/image
     *
     * @return null|string
     */
    public function get_original_url();

}
