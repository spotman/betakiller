<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Twig view
 */
class Kohana_Twig extends View
{

    /**
     * Twig environment
     */
    protected static $_environment;

    /**
     * Initialize the cache directory
     *
     * @param   string $path Path to the cache directory
     *
     * @return  boolean
     */
    protected function _init_cache($path, $chmod)
    {
        if (!@mkdir($path, $chmod, true) && !is_dir($path)) {
            return false;
        }

        return chmod($path, $chmod);
    }

    /**
     * Create a Twig view instance
     *
     * @param   string $file Name of Twig template
     * @param   array  $data Data to be passed to template
     *
     * @return  Twig    Twig view instance
     */
    public static function factory($file = null, array $data = null)
    {
        return new Twig($file, $data);
    }

    /**
     * Create a new Twig environment
     *
     * @return  Twig_Environment  Twig environment
     * @throws Kohana_Exception
     * @throws Twig_Exception
     */
    protected function env()
    {
        $config     = Kohana::$config->load('twig');
        $env_config = $config->get('environment');
        $path       = $env_config['cache'];
        $chmod      = $env_config['chmod'];

        if ($path !== false && !is_writable($path) && !$this->_init_cache($path, $chmod)) {
            throw new Kohana_Exception('Directory :dir must exist and be writable', [
                ':dir' => Debug::path($path),
            ]);
        }

        $loader = new Twig_Loader_CFS($config->get('loader'));
        $env    = new Twig_Environment($loader, $env_config);

        /** @var string[] $functions */
        $functions = $config->get('functions');

        /** @var string[] $filters */
        $filters = $config->get('filters');

        /** @var string[] $tests */
        $tests = $config->get('tests');

        /** @var string[] $extensions */
        $extensions = $config->get('extensions');

        foreach ($functions as $key => $value) {
            $function = new Twig_SimpleFunction($key, $value);
            $env->addFunction($function);
        }

        foreach ($filters as $key => $value) {
            $filter = new Twig_SimpleFilter($key, $value);
            $env->addFilter($filter);
        }

        foreach ($tests as $key => $value) {
            $test = new Twig_SimpleTest($key, $value);
            $env->addTest($test);
        }

        foreach ($extensions as $extension) {
            // Extension is a class name
            if (is_string($extension)) {
                $extension = new $extension;
            } // Extension is lambda
            elseif (is_callable($extension)) {
                $extension = call_user_func($extension);
            }

            if (!($extension instanceof Twig_ExtensionInterface)) {
                throw new Twig_Exception('Extension must be instance of :must, but :real given', [
                    ':must' => Twig_ExtensionInterface::class,
                    ':real' => get_class($extension),
                ]);
            }

            $env->addExtension($extension);
        }

        return $env;
    }

    /**
     * Get the Twig environment (or create it on first call)
     *
     * @return  Twig_Environment  Twig environment
     */
    protected function environment()
    {
        if (static::$_environment === null) {
            static::$_environment = $this->env();
        }

        return static::$_environment;
    }

    /**
     * Set the filename for the Twig view
     *
     * @param   string $file Base name of template
     *
     * @return  Twig    This Twig instance
     */
    public function set_filename($file)
    {
        $this->_file = $file;

        return $this;
    }

    /**
     * Render Twig template as string
     *
     * @param   string $file Base name of template
     *
     * @return  string  Rendered Twig template
     * @throws View_Exception
     */
    public function render($file = null)
    {
        if ($file !== null) {
            $this->set_filename($file);
        }

        $env = $this->environment();

        // Bind global data to Twig environment.
        foreach (static::$_global_data as $key => $value) {
            $env->addGlobal($key, $value);
        }

        return $env->render($this->_file, $this->_data);
    }

} // End Twig
