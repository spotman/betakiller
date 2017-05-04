<?php
namespace BetaKiller\Assets\Model;

use BetaKiller\Assets\Provider\AbstractAssetsProvider;
use BetaKiller\Assets\Provider\AbstractAssetsProviderImage;
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

        return $this->makeHash();
    }

    /**
     * Returns file`s hash string
     *
     * @return string
     */
    public function getHash()
    {
        return $this->get('hash');
    }

    /**
     * Creates unique hash from original filename and stores it in `hash` property
     *
     * @return $this
     */
    private function makeHash()
    {
        $hash = $this->getHash() ?: md5(microtime().$this->getOriginalName());

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
        return $this->getAbPath();
    }

    /**
     * Returns file model url (for deploy url and deploy path)
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getAbPath('/');
    }

    /**
     * Returns deep path for current model (f0/a4/f0a435a89cc65a93d341)
     *
     * @param string $delimiter
     *
     * @return string
     */
    protected function getAbPath($delimiter = DIRECTORY_SEPARATOR)
    {
        $hash   = $this->getHash();
        $depth  = $this->getAbDepth();
        $length = $this->getAbPartLength();

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
    protected function getAbDepth()
    {
        return 2;
    }

    /**
     * How many letters are in path part
     *
     * @return int
     */
    protected function getAbPartLength()
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
        return $this->getProvider()->getUploadUrl();
    }

    /**
     * Returns URL to original file/image
     *
     * @return null|string
     */
    public function getOriginalUrl()
    {
        return $this->loaded()
            ? $this->getProvider()->getOriginalUrl($this)
            : null;
    }

    public function delete()
    {
        // Removing file from storage
        $this->getProvider()->delete($this);

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
     * @return AbstractAssetsProvider|AbstractAssetsProviderImage
     */
    abstract protected function getProvider();
}
