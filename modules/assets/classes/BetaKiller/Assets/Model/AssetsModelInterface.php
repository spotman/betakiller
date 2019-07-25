<?php
namespace BetaKiller\Assets\Model;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Interface AssetsModelInterface
 *
 * Abstract model interface for asset file
 */
interface AssetsModelInterface extends AbstractEntityInterface, HashBasedAssetsModelInterface
{
    /**
     * Returns User model, who uploaded the file
     *
     * @return UserInterface
     */
    public function getUploadedBy(): UserInterface;

    /**
     * Sets user, who uploaded the file
     *
     * @param UserInterface $user
     *
     * @return AssetsModelInterface
     */
    public function setUploadedBy(UserInterface $user): AssetsModelInterface;

    /**
     * Returns the date and time when asset was uploaded
     *
     * @return \DateTimeImmutable
     */
    public function getUploadedAt(): DateTimeImmutable;

    /**
     * Sets the date and time when asset was uploaded
     *
     * @param \DateTimeInterface $time
     *
     * @return AssetsModelInterface
     */
    public function setUploadedAt(DateTimeInterface $time): AssetsModelInterface;

    /**
     * Returns the date and time when asset was last modified
     *
     * @return \DateTimeImmutable
     */
    public function getLastModifiedAt(): DateTimeImmutable;

    /**
     * Sets the date and time when asset was last modified
     *
     * @param \DateTimeInterface $time
     *
     * @return AssetsModelInterface
     */
    public function setLastModifiedAt(DateTimeInterface $time): AssetsModelInterface;

    /**
     * Returns original file name (user-defined filename of uploaded file)
     *
     * @return string
     */
    public function getOriginalName(): string;

    /**
     * Stores original file name (user-defined filename of uploaded file)
     *
     * @param string $name
     *
     * @return AssetsModelInterface
     */
    public function setOriginalName(string $name): AssetsModelInterface;

    /**
     * Returns unique hash
     *
     * @return string|null
     */
    public function getHash(): ?string;

    /**
     * Stores unique hash
     *
     * @param string $hash
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface
     */
    public function setHash(string $hash): AssetsModelInterface;

    /**
     * Returns MIME-type of the file
     *
     * @return string
     */
    public function getMime(): string;

    /**
     * Sets MIME-type of the file
     *
     * @param string $mime
     *
     * @return AssetsModelInterface
     */
    public function setMime(string $mime): AssetsModelInterface;

    /**
     * Returns file size in bytes
     *
     * @return integer
     */
    public function getSize(): int;

    /**
     * Stores file size in bytes
     *
     * @param integer $size
     *
     * @return AssetsModelInterface
     */
    public function setSize(int $size): AssetsModelInterface;
}
