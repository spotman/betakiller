<?php
namespace BetaKiller\Twig;

use Kohana;
use Twig\Loader\FilesystemLoader;

/**
 * Twig loader for Kohana's cascading filesystem
 */
final class TwigLoaderCfs extends FilesystemLoader
{
    private $pathsCacheKey = 'twig_cfs_loader_paths';

    /**
     * @var array Loader configuration
     */
    private $config;

    /**
     * Constructor
     *
     * @param array $config Loader configuration
     */
    public function __construct($config)
    {
        // No paths by default
        parent::__construct();

        $this->config = $config;

        $this->paths = $this->getPathCache();

        if (!$this->paths) {
            $this->addPaths();
            $this->setPathCache($this->paths);
        }
    }

    private function getPathCache(): ?array
    {
        $data = Kohana::cache($this->pathsCacheKey);

        // Foolproof check for missing cache-file and other errors
        if (!is_array($data)) {
            return null;
        }

        return $data;
    }

    private function setPathCache(array $paths): void
    {
        Kohana::cache($this->pathsCacheKey, $paths);
    }

    /**
     * Adds Kohana::include_paths() to Twig Filesystem Loader
     * Supports namespaces (directory aliases starting with @)
     * More info about namespaces here http://twig.sensiolabs.org/doc/api.html
     */
    private function addPaths(): void
    {
        /** @var string[] $namespaces */
        $namespaces  = $this->config['namespaces'];
        $prototypeNs = $this->config['prototype_namespace'];

        $includePaths = Kohana::include_paths();

        // Detect app path (it always placed first)
        $appPath = $includePaths[0];

        // Iterate through Kohana include paths
        foreach ($includePaths as $kohanaPath) {
            $basePath = $kohanaPath.$this->config['path'];

            // Ignore modules without Twig views
            if (!file_exists($basePath)) {
                continue;
            }

            $this->addPath($basePath);

            // Skip application or site-related path
            if (strpos($basePath, $appPath) === false) {
                // Add @proto namespace for views in modules
                $this->addPath($basePath, $prototypeNs);
            }

            foreach ($namespaces as $nsName => $fsAlias) {
                $nsPath = $basePath.DIRECTORY_SEPARATOR.$fsAlias;

                // Ignore modules without Twig namespace directory
                if (!file_exists($nsPath)) {
                    continue;
                }

                $this->addPath($nsPath, $nsName);
            }
        }
    }

    /**
     * Checks if the template can be found.
     *
     * @param string $name  The template name
     * @param bool   $throw Whether to throw an exception when an error occurs
     *
     * @return string|false The template name or false
     */
    protected function findTemplate($name, $throw = true)
    {
        // Add extension to files
        $name .= '.'.$this->config['extension'];

        return parent::findTemplate($name, $throw);
    }
}
