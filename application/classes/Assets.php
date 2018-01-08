<?php

/**
 * Class Assets
 */
class Assets
{
    use \BetaKiller\Utils\Instance\SingletonTrait;

    /**
     * @var \JS
     */
    protected $js;

    /**
     * @var \CSS
     */
    protected $css;

    protected $_config;

    /**
     * Assets constructor.
     *
     * @param \JS  $js
     * @param \CSS $css
     */
    public function __construct(\JS $js, \CSS $css)
    {
        $this->js  = $js;
        $this->css = $css;
    }

    /**
     * @param string $name
     *
     * @return $this
     * @throws \HTTP_Exception_500
     */
    public function add(string $name): Assets
    {
        $methodName = $this->makeMethodName($name);

        // Search for specific method in current class
        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            $config = $this->config()->get($name);

            if (!$config) {
                throw new HTTP_Exception_500('Unknown asset :name', [':name' => $name]);
            }

            // TODO process dependencies

            if (isset($config['js'])) {
                $this->processStatic($this->js, $methodName, $config['js']);
            }

            if (isset($config['css'])) {
                $this->processStatic($this->css, $methodName, $config['css']);
            }
        }

        return $this;
    }

    protected function config()
    {
        if (!$this->_config) {
            $this->_config = Kohana::config('assets');
        }

        return $this->_config;
    }

    /**
     * @param JS|CSS $object
     * @param string $methodName
     * @param mixed  $files
     *
     * @throws HTTP_Exception_500
     */
    protected function processStatic($object, $methodName, $files): void
    {
        // Search for specific method in object
        if ($files === true && method_exists($object, $methodName)) {
            $object->$methodName();
        } else {
            if ($files !== true) {
                if (!is_array($files)) {
                    $files = (array)$files;
                }

                foreach ($files as $file) {
                    $object->addStatic($file);
                }
            } else {
                throw new HTTP_Exception_500('Can not process asset static :method', [':method' => $methodName]);
            }
        }
    }

    protected function makeMethodName($name)
    {
        return str_replace('.', '_', $name);
    }
}
