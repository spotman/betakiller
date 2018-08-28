<?php

use BetaKiller\Assets\AssetsException;
use BetaKiller\Config\ConfigGroupInterface;
use BetaKiller\Config\ConfigProviderInterface;

/**
 * Class Assets
 */
class Assets
{
    /**
     * @var \JS
     */
    protected $js;

    /**
     * @var \CSS
     */
    protected $css;

    /**
     * @var ConfigGroupInterface
     */
    protected $config;

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $configProvider;

    /**
     * Assets constructor.
     *
     * @param \JS                                        $js
     * @param \CSS                                       $css
     * @param \BetaKiller\Config\ConfigProviderInterface $configProvider
     */
    public function __construct(\JS $js, \CSS $css, ConfigProviderInterface $configProvider)
    {
        $this->js             = $js;
        $this->css            = $css;
        $this->configProvider = $configProvider;
    }

    /**
     * @param string $name
     *
     * @return $this
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function add(string $name): Assets
    {
        $methodName = $this->makeMethodName($name);

        // Search for specific method in current class
        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            $config = $this->config()[$name];

            if (!$config) {
                throw new AssetsException('Unknown asset :name', [':name' => $name]);
            }

            if (isset($config['js'])) {
                $this->processStatic($this->js, $methodName, $config['js']);
            }

            if (isset($config['css'])) {
                $this->processStatic($this->css, $methodName, $config['css']);
            }
        }

        return $this;
    }

    /**
     * @return array
     * @throws \BetaKiller\Assets\AssetsException
     */
    protected function config(): array
    {
        if (!$this->config) {
            $this->config = $this->configProvider->load(['assets']);

            if (!$this->config) {
                throw new AssetsException('Missing assets config');
            }
        }

        return $this->config->asArray();
    }

    /**
     * @param \CommonStaticInterface $object
     * @param string                 $methodName
     * @param mixed                  $files
     *
     * @throws \BetaKiller\Assets\AssetsException
     */
    protected function processStatic(CommonStaticInterface $object, string $methodName, $files): void
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
                throw new AssetsException('Can not process asset static :method', [':method' => $methodName]);
            }
        }
    }

    protected function makeMethodName($name)
    {
        return str_replace('.', '_', $name);
    }
}
