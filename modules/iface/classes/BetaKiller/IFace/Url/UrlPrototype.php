<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Utils;
use BetaKiller\Exception;

class UrlPrototype
{
    use Utils\Instance\Simple;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var string
     */
    protected $modelKey;

    /**
     * @var bool
     */
    protected $isMethodCall = false;

    /**
     * @param string $modelKey
     */
    public function setModelKey($modelKey)
    {
        $this->modelKey = $modelKey;
    }

    /**
     * @return string
     */
    public function getModelKey()
    {
        return $this->modelKey;
    }

    /**
     * @param string $modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @return bool
     */
    public function isMethodCall()
    {
        return $this->isMethodCall;
    }

    public function parse($string)
    {
        if (!$string) {
            throw new Exception('Empty url prototype string');
        }

        $string = trim($string, '{}');

        $model = explode('.', $string);
        $name  = $model[0];
        $key   = $model[1];

        if (strpos($key, '()') !== false) {
            $this->isMethodCall = true;
            $key                = str_replace('()', '', $key);
        }

        $this->setModelName($name);
        $this->setModelKey($key);

        return $this;
    }
}
