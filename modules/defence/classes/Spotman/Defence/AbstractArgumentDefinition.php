<?php
declare(strict_types=1);

namespace Spotman\Defence;

abstract class AbstractArgumentDefinition implements ArgumentDefinitionInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $type;

    /**
     * @var bool
     */
    private bool $optional = false;

    /**
     * @var bool
     */
    private bool $nullable = false;

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
     * @return bool
     */
    public function hasDefaultValue(): bool
    {
        return $this->defaultValue !== null;
    }

    /**
     * Returns true if rule defines nullable argument
     *
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Defines nullable argument
     */
    public function markAsNullable(): void
    {
        $this->nullable = true;
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
     * Returns true if rule defines a float argument
     *
     * @return bool
     */
    public function isFloat(): bool
    {
        return $this->getType() === self::TYPE_FLOAT;
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
     * @inheritDoc
     */
    public function isDate(): bool
    {
        return $this->getType() === self::TYPE_DATE;
    }

    /**
     * Returns true if rule defines a string containing datetime
     *
     * @return bool
     */
    public function isDateTime(): bool
    {
        return $this->getType() === self::TYPE_DATETIME;
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
     * @inheritDoc
     */
    public function isParameter(): bool
    {
        return $this instanceof ParameterArgumentDefinitionInterface;
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

    /**
     * @return bool
     */
    public function mayHaveDefaultValue(): bool
    {
        // Only scalar/array types may have a default value
        return $this->isScalar() || $this->isArray();
    }

    /**
     * Returns true if argument is scalar (has type of int|string|bool|identity)
     *
     * @return bool
     */
    public function isScalar(): bool
    {
        return $this instanceof SingleArgumentDefinitionInterface;
    }

    /**
     * @inheritDoc
     */
    public function isArray(): bool
    {
        return $this instanceof SingleArrayArgumentDefinitionInterface;
    }
}
