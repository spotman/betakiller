<?php
namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Model\HashBasedAssetsModelInterface;
use BetaKiller\Helper\AssetsHelper;
use DateTimeImmutable;
use ORM;
use Spotman\Api\ApiResponseItemInterface;

/**
 * Class AbstractOrmBasedAssetsModel
 *
 * Abstract class for all ORM-based asset models
 */
abstract class AbstractOrmBasedAssetsModel extends ORM implements AssetsModelInterface, HashBasedAssetsModelInterface,
    ApiResponseItemInterface
{
    public const COL_UPLOADED_BY = 'uploaded_by';

    private const MAX_LENGTH_ORIGINAL_NAME = 64;

    protected function configure(): void
    {
        $this->belongs_to([
            'uploaded_by_user' => [
                'model'       => User::getModelName(),
                'foreign_key' => self::COL_UPLOADED_BY,
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
        $pathName = \pathinfo($name, \PATHINFO_FILENAME);
        $pathExt = \pathinfo($name, \PATHINFO_EXTENSION);

        $maxLength = self::MAX_LENGTH_ORIGINAL_NAME - \mb_strlen($pathExt) - 1; // minus "dot" symbol

        // Shrink too long name to prevent DB errors
        $name = substr($pathName, 0, $maxLength).'.'.$pathExt;

        $this->setOnce('original_name', $name);

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
     * Returns unique hash for provided content
     *
     * @param string $hash
     *
     * @return void
     */
    public function setHash(string $hash): void
    {
        $this->setOnce('hash', $hash);
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
        $this->setOnce('mime', $mime);

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
        $this->setOnce('uploaded_by_user', $user);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isUploadedBy(UserInterface $user): bool
    {
        return $this->getUploadedBy()->getID() === $user->getID();
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
     * @param \DateTimeImmutable $time
     *
     * @return AssetsModelInterface
     */
    public function setUploadedAt(DateTimeImmutable $time): AssetsModelInterface
    {
        $this->set_datetime_column_value('uploaded_at', $time);

        return $this;
    }

    /**
     * Returns the date and time when asset was modified
     *
     * @return DateTimeImmutable
     */
    public function getLastModifiedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('last_modified_at');
    }

    /**
     * Sets the date and time when asset was modified
     *
     * @param \DateTimeImmutable $time
     *
     * @return AssetsModelInterface
     */
    public function setLastModifiedAt(DateTimeImmutable $time): AssetsModelInterface
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
        $this->setOnce('size', $size);

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
        return $this->getLastModifiedAt();
    }
}
