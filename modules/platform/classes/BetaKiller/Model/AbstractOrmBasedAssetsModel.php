<?php
namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Helper\AssetsHelper;
use DateTimeImmutable;
use DateTimeInterface;
use ORM;
use Spotman\Api\ApiResponseItemInterface;

/**
 * Class AbstractOrmBasedAssetsModel
 *
 * Abstract class for all ORM-based asset models
 */
abstract class AbstractOrmBasedAssetsModel extends ORM implements AssetsModelInterface, ApiResponseItemInterface
{
    protected function configure(): void
    {
        $this->belongs_to([
            'uploaded_by_user' => [
                'model'       => 'User',
                'foreign_key' => 'uploaded_by',
            ],
        ]);
    }

    /**
     * Returns original file name (user-defined filename of uploaded file)
     *
     * @return string
     */
    public function getOriginalName(): string
    {
        return (string)$this->get('original_name');
    }

    /**
     * Stores original file name (user-defined filename of uploaded file)
     *
     * @param string $name
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function setOriginalName(string $name): AssetsModelInterface
    {
        $this->set('original_name', $name);

        return $this;
    }

    /**
     * Returns file`s hash string
     *
     * @return string|null
     */
    public function getHash(): ?string
    {
        return $this->get('hash');
    }

    /**
     * Stores unique hash
     *
     * @param string $hash
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function setHash(string $hash): AssetsModelInterface
    {
        $this->set('hash', $hash);

        return $this;
    }

    /**
     * Returns MIME-type of the file
     *
     * @return string
     */
    public function getMime(): string
    {
        return $this->get('mime');
    }

    /**
     * Sets MIME-type of the file
     *
     * @param string $mime
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function setMime(string $mime): AssetsModelInterface
    {
        $this->set('mime', $mime);

        return $this;
    }

    /**
     * Returns User model, who uploaded the file
     *
     * @return UserInterface
     */
    public function getUploadedBy(): UserInterface
    {
        return $this->get('uploaded_by_user');
    }

    /**
     * Sets user, who uploaded the file
     *
     * @param UserInterface $user
     *
     * @return AssetsModelInterface
     */
    public function setUploadedBy(UserInterface $user): AssetsModelInterface
    {
        $this->set('uploaded_by_user', $user);

        return $this;
    }

    /**
     * Returns the date and time when asset was uploaded
     *
     * @return DateTimeImmutable
     */
    public function getUploadedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('uploaded_at');
    }

    /**
     * Sets the date and time when asset was uploaded
     *
     * @param \DateTimeInterface $time
     *
     * @return AssetsModelInterface
     */
    public function setUploadedAt(DateTimeInterface $time): AssetsModelInterface
    {
        $this->set_datetime_column_value('uploaded_at', $time);

        return $this;
    }

    /**
     * Returns the date and time when asset was modified
     *
     * @return DateTimeImmutable|null
     * @throws \Kohana_Exception
     */
    public function getLastModifiedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('last_modified_at');
    }

    /**
     * Sets the date and time when asset was modified
     *
     * @param \DateTimeInterface $time
     *
     * @return AssetsModelInterface
     */
    public function setLastModifiedAt(DateTimeInterface $time): AssetsModelInterface
    {
        $this->set_datetime_column_value('last_modified_at', $time);

        return $this;
    }

    /**
     * Returns file size in bytes
     *
     * @return integer
     */
    public function getSize(): int
    {
        return (int)$this->get('size');
    }

    /**
     * Stores file size in bytes
     *
     * @param integer $size
     *
     * @return AssetsModelInterface
     */
    public function setSize(int $size): AssetsModelInterface
    {
        $this->set('size', $size);

        return $this;
    }

    /**
     * @return callable
     */
    public function getApiResponseData(): callable
    {
        return function (AssetsHelper $helper) {
            return $helper->getInfo($this);
        };
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getApiLastModified(): ?DateTimeImmutable
    {
        return null;
    }
}
