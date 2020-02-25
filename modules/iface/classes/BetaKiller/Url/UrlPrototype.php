<?php
namespace BetaKiller\Url;

final class UrlPrototype
{
    public const  REGEX              = '/\{[A-Z]{1}[A-Za-z_]+(\.[A-Za-z_]+(\(\)){0,1}){0,1}\}/';
    private const KEY_SEPARATOR      = '.';
    private const METHOD_CALL_MARKER = '()';

    /**
     * @var string
     */
    private $modelName;

    /**
     * @var string
     */
    private $modelKey;

    /**
     * @var bool
     */
    private $isMethodCall = false;

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

        $parsed    = explode(self::KEY_SEPARATOR, $string, 2);
        $modelName = $parsed[0];
        $keyPart   = $parsed[1] ?? null;

        $key          = str_replace(self::METHOD_CALL_MARKER, '', $keyPart);
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
    public function isRawParameter(): bool
    {
        return !$this->hasModelKey();
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
        $str = $this->getDataSourceName();

        if ($this->hasModelKey()) {
            $str .= self::KEY_SEPARATOR.$this->getModelKey();
        }

        if ($this->isMethodCall()) {
            $str .= self::METHOD_CALL_MARKER;
        }

        return $str;
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
