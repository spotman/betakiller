<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Assets
 */

class Assets {

    use \BetaKiller\Utils\Instance\Singleton;

    protected $_config;

    public function add($name)
    {
        $method_name = $this->make_method_name($name);

        // Search for specific method in current class
        if ( method_exists($this, $method_name) )
        {
            $this->$method_name();
        }
        else
        {
            $config = $this->config()->get($name);

            if ( ! $config )
                throw new HTTP_Exception_500('Unknown asset :name', array(':name' => $name));

            // TODO process dependencies

            if ( isset($config['js']) )
            {
                $this->process_static($this->js(), $method_name, $config['js']);
            }

            if ( isset($config['css']) )
            {
                $this->process_static($this->css(), $method_name, $config['css']);
            }
        }

        return $this;
    }

    protected function config()
    {
        if ( ! $this->_config )
        {
            $this->_config = Kohana::config('assets');
        }

        return $this->_config;
    }

    protected function css()
    {
        return CSS::instance();
    }

    protected function js()
    {
        return JS::instance();
    }

    /**
     * @param JS|CSS $object
     * @param string $method_name
     * @param mixed $files
     * @throws HTTP_Exception_500
     */
    protected function process_static($object, $method_name, $files)
    {
        // Search for specific method in object
        if ( $files === TRUE AND method_exists($object, $method_name) )
        {
            $object->$method_name();
        }
        else if ( $files !== TRUE )
        {
            if ( ! is_array($files) )
            {
                $files = array($files);
            }

            foreach ( $files as $file )
            {
                $object->add($file);
            }
        }
        else
            throw new HTTP_Exception_500('Can not process asset static :method', array(':method' => $method_name));
    }

    protected function make_method_name($name)
    {
        return str_replace('.', '_', $name);
    }
}
