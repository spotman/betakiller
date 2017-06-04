<?php
namespace BetaKiller\Assets\Model;

use Assets_Provider_Image;
use BetaKiller\Assets\AssetsModelInterface;
use BetaKiller\Assets\AssetsProvider;
use BetaKiller\Model\UserInterface;
use DateTime;
use ORM;

/**
 * Class AbstractAssetsOrmModel
 *
 * Abstract class for all ORM-based asset models
 */
abstract class AbstractAssetsOrmModel extends ORM implements AssetsModelInterface
{

    protected function _initialize()
    {
        $this->belongs_to([
            'uploaded_by_user' => [
                'model'       => 'User',
                'foreign_key' => 'uploaded_by',
            ],
        ]);

        parent::_initialize();
    }

    /**
     * Returns original file name (user-defined filename of uploaded file)
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->get('original_name');
    }

    /**
     * Stores original file name (user-defined filename of uploaded file)
     *
     * @param $name
     *
     * @return $this
     */
    public function setOriginalName($name)
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
     *
     * @return $this
     */
    protected function make_hash()
    {
        $hash = $this->get_hash() ?: md5(microtime().$this->getOriginalName());

        return $this->set('hash', $hash);
    }

    /**
     * Returns MIME-type of the file
     *
     * @return string
     */
    public function getMime()
    {
        return $this->get('mime');
    }

    /**
     * Sets MIME-type of the file
     *
     * @param string $mime
     *
     * @return $this
     */
    public function setMime($mime)
    {
        return $this->set('mime', $mime);
    }

    /**
     * Returns User model, who uploaded the file
     *
     * @return UserInterface
     */
    public function getUploadedBy()
    {
        return $this->get('uploaded_by_user');
    }

    /**
     * Sets user, who uploaded the file
     *
     * @param UserInterface $user
     *
     * @return $this
     */
    public function setUploadedBy(UserInterface $user)
    {
        return $this->set('uploaded_by_user', $user);
    }

    /**
     * Returns the date and time when asset was uploaded
     *
     * @return DateTime|NULL
     */
    public function getUploadedAt()
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
    public function setUploadedAt(DateTime $time)
    {
        return $this->set_datetime_column_value('uploaded_at', $time);
    }

    /**
     * Returns the date and time when asset was modified
     *
     * @return DateTime|NULL
     */
    public function getLastModifiedAt()
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
    public function setLastModifiedAt(DateTime $time)
    {
        return $this->set_datetime_column_value('last_modified_at', $time);
    }

    /**
     * Returns file size in bytes
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->get('size');
    }

    /**
     * Stores file size in bytes
     *
     * @param integer $size
     *
     * @return $this
     */
    public function setSize($size)
    {
        return $this->set('size', $size);
    }

    /**
     * Returns path for file in storage
     *
     * @return string
     */
    public function getStorageFileName()
    {
        return $this->get_ab_path();
    }

    /**
     * Returns file model url (for deploy url and deploy path)
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->get_ab_path('/');
    }

    /**
     * Returns deep path for current model (f0/a4/f0a435a89cc65a93d341)
     *
     * @param string $delimiter
     *
     * @return string
     */
    protected function get_ab_path($delimiter = DIRECTORY_SEPARATOR)
    {
        $hash   = $this->get_hash();
        $depth  = $this->get_ab_depth();
        $length = $this->get_ab_part_length();

        $parts = [];

        for ($i = 0; $i < $depth; $i++) {
            $parts[] = substr($hash, $i * $length, $length);
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
     *
     * @return AssetsModelInterface|NULL
     */
    public function byUrl($url)
    {
        $hash = basename($url);

        /** @var AbstractAssetsOrmModel $model */
        $model = $this->model_factory()->where('hash', '=', $hash)->find();

        return $model->loaded() ? $model : null;
    }

    /**
     * Returns URL for uploading new assets
     *
     * @return string
     */
    public function getUploadUrl()
    {
        return $this->get_provider()->getUploadUrl();
    }

    /**
     * Returns URL to original file/image
     *
     * @return null|string
     */
    public function getOriginalUrl()
    {
        return $this->loaded()
            ? $this->get_provider()->getOriginalUrl($this)
            : null;
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
    public function toJson()
    {
        return $this->as_array();
    }

    /**
     * Returns assets provider associated with current model
     *
     * @return AssetsProvider|Assets_Provider_Image
     */
    abstract protected function get_provider();

}
