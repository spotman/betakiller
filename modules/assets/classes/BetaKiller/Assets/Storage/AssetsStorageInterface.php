<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\Model\AssetsModelInterface;

interface AssetsStorageInterface
{
    /**
     * Model path would be prepended with this
     *
     * @param string $path
     */
    public function setBasePath(string $path): void;

    /**
     * @param AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function get(AssetsModelInterface $model): string;

    /**
     * Stores file
     *
     * @param AssetsModelInterface $model
     * @param string               $content
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function put(AssetsModelInterface $model, string $content): void;

    /**
     * Deletes the file
     *
     * @param AssetsModelInterface $model
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function delete(AssetsModelInterface $model): void;
}
