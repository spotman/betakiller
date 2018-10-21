<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Twig loader for Kohana's cascading filesystem
 */
class Kohana_Twig_Loader_CFS extends Twig_Loader_Filesystem
{

    protected $_paths_cache_key = 'twig_cfs_loader_paths';

    /**
     * Loader configuration
     */
    protected $_config;

    /**
     * Constructor
     *
     * @param  array $config Loader configuration
     */
    public function __construct($config)
    {
        // No paths by default
        parent::__construct();

        $this->_config = $config;

        $this->paths = $this->path_cache($this->_paths_cache_key);

        if (!$this->paths) {
            $this->add_paths();
            $this->path_cache($this->_paths_cache_key, $this->paths);
        }
    }

    /**
     * Getter/setter for caching view paths
     * If you are using another cache module, override this method in Twig_Loader_CFS
     *
     * @param string     $key
     * @param mixed|null $value
     *
     * @return bool|null
     */
    protected function path_cache($key, $value = null)
    {
        return ($value === null)
            ? Kohana::cache($key)
            : Kohana::cache($key, $value);
    }

    /**
     * Adds Kohana::include_paths() to Twig Filesystem Loader
     * Supports namespaces (directory aliases starting with @)
     * More info about namespaces here http://twig.sensiolabs.org/doc/api.html
     */
    protected function add_paths()
    {
        /** @var string[] $namespaces */
        $namespaces   = $this->_config['namespaces'];
        $prototype_ns = $this->_config['prototype_namespace'];

        $include_paths = Kohana::include_paths();

        // Detect app path (it always placed first)
        $app_path = $include_paths[0];

        // Iterate through Kohana include paths
        foreach ($include_paths as $path_index => $kohana_path) {
            $base_path = $kohana_path.$this->_config['path'];

            // Ignore modules without Twig views
            if (!file_exists($base_path)) {
                continue;
            }

            $this->addPath($base_path);

            // Skip application or site-related path
            if (strpos($base_path, $app_path) === false) {
                // Add @proto namespace for views in modules
                $this->addPath($base_path, $prototype_ns);
            }

            foreach ($namespaces as $ns_name => $fs_alias) {
                $ns_path = $base_path.DIRECTORY_SEPARATOR.$fs_alias;

                // Ignore modules without Twig namespace directory
                if (!file_exists($ns_path)) {
                    continue;
                }

                $this->addPath($ns_path, $ns_name);
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
        $name .= '.'.$this->_config['extension'];

        return parent::findTemplate($name, $throw);
    }

} // End CFS
