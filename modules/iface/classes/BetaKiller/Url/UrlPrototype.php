<?php
namespace BetaKiller\Url;

final readonly class UrlPrototype
{
    public const KEY_ID = 'id';

    public const  REGEX              = '/{([A-Z][A-Za-z_]+)(\.([A-Za-z_]+)(\(\))?)?}/';
    private const KEY_SEPARATOR      = '.';
    private const METHOD_CALL_MARKER = '()';

    /**
     * UrlPrototype constructor.
     *
     * @param string      $modelName
     * @param string|null $modelKey
     * @param bool        $isMethodCall
     *
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function __construct(private string $modelName, private ?string $modelKey, private bool $isMethodCall)
    {
        if (!$this->modelKey && $this->isMethodCall) {
            throw new UrlPrototypeException('Missing model key in ":model" method call', [
                ':value' => $this->modelName,
            ]);
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

        if (!str_starts_with($string, '{') || !str_ends_with($string, '}')) {
            throw new UrlPrototypeException('Prototype string ":value" must be surrounded by curly braces', [
                ':value' => $string,
            ]);
        }

        if (!preg_match(self::REGEX, $string, $matches, PREG_UNMATCHED_AS_NULL)) {
            throw new UrlPrototypeException('Malformed UrlPrototype string ":value"', [
                ':value' => $string,
            ]);
        }

        $modelName    = $matches[1];
        $key          = $matches[3];
        $isMethodCall = !empty($matches[4]);

        return new self($modelName, $key, $isMethodCall);
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
        return $this->modelKey === self::KEY_ID;
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

            if ($this->isMethodCall()) {
                $str .= self::METHOD_CALL_MARKER;
            }
        }

        return '{'.$str.'}';
    }
}
