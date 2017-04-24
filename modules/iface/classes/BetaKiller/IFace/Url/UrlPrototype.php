<?php
namespace BetaKiller\IFace\Url;

class UrlPrototype
{
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
     * @return $this
     */
    public function setModelKey($modelKey)
    {
        $this->modelKey = $modelKey;
        return $this;
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
     * @return $this
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
        return $this;
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

    /**
     * @return $this
     */
    public function markAsMethodCall()
    {
        return $this->setIsMethodCall(true);
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    private function setIsMethodCall($value)
    {
        $this->isMethodCall = (bool)$value;
        return $this;
    }
}
