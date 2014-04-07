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
    public function get_filename();

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

}