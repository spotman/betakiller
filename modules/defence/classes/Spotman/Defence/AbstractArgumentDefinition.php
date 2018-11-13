<?php
declare(strict_types=1);

namespace Spotman\Defence;

abstract class AbstractArgumentDefinition implements ArgumentDefinitionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $optional = false;

    /**
     * @var mixed|null
     */
    private $defaultValue;

    /**
     * ArgumentDefinition constructor.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        if (!\in_array($type, self::ALLOWED_TYPES, true)) {
            throw new \LogicException(\sprintf('Unknown argument type "%s"', $type));
        }

        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     *
     */
    public function markAsOptional(): void
    {
        $this->optional = true;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $value
     */
    public function setDefaultValue($value): void
    {
        $this->defaultValue = $value;
    }

    /**
     * Returns true if rule defines identity argument
     *
     * @return bool
     */
    public function isIdentity(): bool
    {
        return $this->getType() === self::TYPE_IDENTITY;
    }

    /**
     * Returns true if rule defines boolean argument
     *
     * @return bool
     */
    public function isBool(): bool
    {
        return $this->getType() === self::TYPE_BOOLEAN;
    }

    /**
     * Returns true if rule defines integer argument
     *
     * @return bool
     */
    public function isInt(): bool
    {
        return $this->getType() === self::TYPE_INTEGER;
    }

    /**
     * Returns true if rule defines string argument
     *
     * @return bool
     */
    public function isString(): bool
    {
        return $this->getType() === self::TYPE_STRING;
    }

    /**
     * Returns true if rule defines a string containing email
     *
     * @return bool
     */
    public function isEmail(): bool
    {
        return $this->getType() === self::TYPE_EMAIL;
    }

    /**
     * Returns true if rule defines string containing html
     *
     * @return bool
     */
    public function isHtml(): bool
    {
        return $this->getType() === self::TYPE_HTML;
    }

    /**
     * Returns true if rule defines array argument
     *
     * @return bool
     */
    public function isComposite(): bool
    {
        return $this->getType() === self::TYPE_COMPOSITE;
    }
}
