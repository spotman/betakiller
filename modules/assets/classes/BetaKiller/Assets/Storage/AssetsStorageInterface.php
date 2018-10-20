<?php
namespace BetaKiller\Assets\Storage;

interface AssetsStorageInterface
{
    /**
     *  Returns true if files are located under document root
     *
     * @return bool
     */
    public function isPublic(): bool;

    /**
     * Model path would be prepended with this
     *
     * @param string $path
     *
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function setBasePath(string $path): void;

    /**
     * @param string $path
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function getFile(string $path): string;

    /**
     * Stores file
     *
     * @param string $path
     * @param string $content
     *
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function putFile(string $path, string $content): void;

    /**
     * Deletes the file
     *
     * @param string $path
     *
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function deleteFile(string $path): void;

    /**
     * Returns array of files in provided directory
     *
     * @param string $directory
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function getFiles(string $directory): array;

    /**
     * Delete provided directory. Throws an exception if there are files inside
     *
     * @param string $path
     *
     * @return void
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function deleteDirectory(string $path): void;
}
