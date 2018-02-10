<?php
namespace BetaKiller\Assets\Storage;

interface AssetsStorageInterface
{
    /**
     * Model path would be prepended with this
     *
     * @param string $path
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function setBasePath(string $path): void;

    /**
     * @return string
     */
    public function getDirectorySeparator(): string;

    /**
     * @param string $path
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function get(string $path): string;

    /**
     * Stores file
     *
     * @param string $path
     * @param string $content
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function put(string $path, string $content): void;

    /**
     * Deletes the file
     *
     * @param string $path
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function delete(string $path): void;

    /**
     * Returns true if storage`s files are located outside of document root and deploy is needed
     *
     * @return bool
     */
    public function isDeployRequired(): bool;
}
