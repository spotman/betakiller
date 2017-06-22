<?php
namespace BetaKiller\Assets;

use BetaKiller\Model\UserInterface;
use DateTime;

/**
 * Interface AssetsModelInterface
 *
 * Abstract model interface for asset file
 */
interface AssetsModelInterface
{
    /**
     * @return int
     */
    public function get_id();

    /**
     * Returns filename for storage
     *
     * @return string
     */
    public function getStorageFileName();

    /**
     * Returns file model url (for deploy url and deploy path)
     *
     * @return string
     */
    public function getUrl();

    /**
     * Performs file model search by url (deploy url dispatching)
     *
     * @param string $url
     *
     * @return AssetsModelInterface|NULL
     */
    public function byUrl($url);

    /**
     * Returns User model, who uploaded the file
     *
     * @return UserInterface
     */
    public function getUploadedBy();

    /**
     * Sets user, who uploaded the file
     *
     * @param UserInterface $user
     *
     * @return $this
     */
    public function setUploadedBy(UserInterface $user);

    /**
     * Returns the date and time when asset was uploaded
     *
     * @return DateTime
     */
    public function getUploadedAt();

    /**
     * Sets the date and time when asset was uploaded
     *
     * @param \DateTime $time
     *
     * @return mixed
     */
    public function setUploadedAt(DateTime $time);

    /**
     * Returns the date and time when asset was last modified
     *
     * @return DateTime|null
     */
    public function getLastModifiedAt();

    /**
     * Sets the date and time when asset was last modified
     *
     * @param \DateTime $time
     *
     * @return mixed
     */
    public function setLastModifiedAt(DateTime $time);

    /**
     * Returns original file name (user-defined filename of uploaded file)
     *
     * @return string
     */
    public function getOriginalName();

    /**
     * Stores original file name (user-defined filename of uploaded file)
     *
     * @param $name
     *
     * @return $this
     */
    public function setOriginalName($name);

    /**
     * Returns MIME-type of the file
     *
     * @return string
     */
    public function getMime();

    /**
     * Sets MIME-type of the file
     *
     * @param string $mime
     *
     * @return $this
     */
    public function setMime($mime);

    /**
     * Returns file size in bytes
     *
     * @return integer
     */
    public function getSize();

    /**
     * Stores file size in bytes
     *
     * @param integer $size
     *
     * @return $this
     */
    public function setSize($size);

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
    public function toJson();

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
    public function getUploadUrl();

    /**
     * Returns URL to original file/image
     *
     * @return null|string
     */
    public function getOriginalUrl();
}
