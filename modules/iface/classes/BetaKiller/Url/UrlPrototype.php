<?php
namespace BetaKiller\Url;

class UrlPrototype
{
    private const KEY_SEPARATOR = '.';
    private const METHOD_CALL_MARKER = '()';

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
     * UrlPrototype constructor.
     *
     * @param string $modelName
     * @param string $modelKey
     * @param bool   $isMethodCall
     */
    public function __construct(string $modelName, string $modelKey, ?bool $isMethodCall = null)
    {
        $this->setModelName($modelName);
        $this->setModelKey($modelKey);

        if ($isMethodCall) {
            $this->markAsMethodCall();
        }
    }

    /**
     * @param string $string
     *
     * @return \BetaKiller\Url\UrlPrototype
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public static function fromString(string $string): UrlPrototype
    {
        if (!$string) {
            throw new UrlPrototypeException('Empty url prototype string');
        }

        if ($string[0] !== '{' || substr($string, -1) !== '}') {
            throw new UrlPrototypeException('Prototype string must be surrounded by curly braces');
        }

        $string = trim($string, '{}');

        $parsed = explode(self::KEY_SEPARATOR, $string, 2);
        $modelName = $parsed[0];
        $keyPart = $parsed[1] ?? null;

        $key = str_replace(self::METHOD_CALL_MARKER, '', $keyPart);
        $isMethodCall = substr($keyPart, -2) === self::METHOD_CALL_MARKER;

        return new self($modelName, $key, $isMethodCall);
    }

    /**
     * @param string $modelKey
     *
     * @return UrlPrototype
     */
    private function setModelKey(string $modelKey): UrlPrototype
    {
        $this->modelKey = $modelKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getModelKey(): string
    {
        return $this->modelKey;
    }

    public function hasIdKey(): bool
    {
        return $this->modelKey === 'id';
    }

    /**
     * @return bool
     */
    public function hasModelKey(): bool
    {
        return !empty($this->modelKey);
    }

    /**
     * @param string $modelName
     *
     * @return UrlPrototype
     */
    private function setModelName(string $modelName): UrlPrototype
    {
        $this->modelName = $modelName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataSourceName(): string
    {
        return $this->modelName;
    }

    /**
     * @return bool
     */
    public function isMethodCall(): bool
    {
        return $this->isMethodCall;
    }

    public function asString(): string
    {
        $str = $this->getDataSourceName().self::KEY_SEPARATOR.$this->getModelKey();

        if ($this->isMethodCall()) {
            $str .= self::METHOD_CALL_MARKER;
        }

        return $str;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString()
    {
        return $this->asString();
    }

    /**
     * @return UrlPrototype
     */
    private function markAsMethodCall(): UrlPrototype
    {
        $this->isMethodCall = true;

        return $this;
    }
}
