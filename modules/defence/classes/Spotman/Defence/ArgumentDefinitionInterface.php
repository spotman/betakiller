<?php
declare(strict_types=1);

namespace Spotman\Defence;

interface ArgumentDefinitionInterface
{
    // Scalar types
    public const TYPE_IDENTITY = 'id';
    public const TYPE_BOOLEAN  = 'bool';
    public const TYPE_INTEGER  = 'int';
    public const TYPE_STRING   = 'string';
    public const TYPE_EMAIL    = 'email';
    public const TYPE_TEXT     = 'text';
    public const TYPE_HTML     = 'html';

    // Named collection of scalars
    public const TYPE_COMPOSITE = 'composite';

    // Indexed array of scalars
    public const TYPE_SINGLE_ARRAY = 'single_array';

    // Indexed array of composites
    public const TYPE_COMPOSITE_ARRAY = 'composite_array';

    public const ALLOWED_TYPES = [
        self::TYPE_IDENTITY,
        self::TYPE_BOOLEAN,
        self::TYPE_INTEGER,
        self::TYPE_STRING,
        self::TYPE_EMAIL,
        self::TYPE_TEXT,
        self::TYPE_HTML,
        self::TYPE_COMPOSITE,
        self::TYPE_SINGLE_ARRAY,
        self::TYPE_COMPOSITE_ARRAY,
    ];

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public function isOptional(): bool;

    /**
     *
     */
    public function markAsOptional(): void;

    /**
     * @return mixed|null
     */
    public function getDefaultValue();

    /**
     * @return bool
     */
    public function hasDefaultValue(): bool;

    /**
     * @param mixed $value
     */
    public function setDefaultValue($value): void;

    /**
     * @return bool
     */
    public function mayHaveDefaultValue(): bool;

    /**
     * Returns true if rule defines identity argument
     *
     * @return bool
     */
    public function isIdentity(): bool;

    /**
     * Returns true if rule defines a boolean argument
     *
     * @return bool
     */
    public function isBool(): bool;

    /**
     * Returns true if rule defines an integer argument
     *
     * @return bool
     */
    public function isInt(): bool;

    /**
     * Returns true if rule defines a string argument
     *
     * @return bool
     */
    public function isString(): bool;

    /**
     * Returns true if argument is scalar (has type of int|string|bool|identity)
     *
     * @return bool
     */
    public function isScalar(): bool;

    /**
     * Returns true if rule defines a string containing html
     *
     * @return bool
     */
    public function isHtml(): bool;

    /**
     * Returns true if rule defines a string containing email
     *
     * @return bool
     */
    public function isEmail(): bool;

    /**
     * Returns true if rule defines an array argument
     *
     * @return bool
     */
    public function isComposite(): bool;
}
