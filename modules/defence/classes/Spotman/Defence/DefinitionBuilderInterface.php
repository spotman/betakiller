<?php
declare(strict_types=1);

namespace Spotman\Defence;

interface DefinitionBuilderInterface
{
    /**
     * Define ID argument
     *
     * @param string|null $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function identity(string $name = null): DefinitionBuilderInterface;

    /**
     * Define int argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function int(string $name): DefinitionBuilderInterface;

    /**
     * Define string argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function string(string $name): DefinitionBuilderInterface;

    /**
     * Define float argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function float(string $name): DefinitionBuilderInterface;

    /**
     * Define string argument containing email
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function email(string $name): DefinitionBuilderInterface;

    /**
     * Define string argument containing multi-line text
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function text(string $name): DefinitionBuilderInterface;

    /**
     * Define string argument containing HTML code
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function html(string $name): DefinitionBuilderInterface;

    /**
     * Define datetime argument (string convert to DateTimeImmutable)
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function datetime(string $name): DefinitionBuilderInterface;

    /**
     * Define bool argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function bool(string $name): DefinitionBuilderInterface;

    /**
     * Define indexed array of integers
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function intArray(string $name): DefinitionBuilderInterface;

    /**
     * Define indexed array of floats
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function floatArray(string $name): DefinitionBuilderInterface;

    /**
     * Define indexed array of strings like ['asd', 'qwe']
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function stringArray(string $name): DefinitionBuilderInterface;

    /**
     * Define indexed array of nested collections like [{}, {}, {}]
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function compositeArrayStart(string $name): DefinitionBuilderInterface;

    /**
     * Define named collection of arguments like {"name": {}}
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function compositeStart(string $name): DefinitionBuilderInterface;

    /**
     * End nested definition
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function compositeEnd(): DefinitionBuilderInterface;

    /**
     * Mark last argument as nullable
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function nullable(): DefinitionBuilderInterface;

    /**
     * Mark last argument as optional
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function optional(): DefinitionBuilderInterface;

    /**
     * Set default value for last argument
     *
     * @param $value
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function default($value): DefinitionBuilderInterface;

    /**
     * Rule helpers below
     */

    /**
     * @param string $pattern
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function regex(string $pattern): DefinitionBuilderInterface;

    /**
     * @param int $min
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function minLength(int $min): DefinitionBuilderInterface;

    /**
     * @param int $max
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function maxLength(int $max): DefinitionBuilderInterface;

    /**
     * @param int $min
     * @param int $max
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function lengthBetween(int $min, int $max): DefinitionBuilderInterface;

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function positive(): DefinitionBuilderInterface;

    /**
     * @param array $allowed
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function whitelist(array $allowed): DefinitionBuilderInterface;

    /**
     * Filter helpers below
     */

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function lowercase(): DefinitionBuilderInterface;

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function uppercase(): DefinitionBuilderInterface;

    /**
     * @param \Spotman\Defence\ArgumentsDefinitionProviderInterface $provider
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function import(ArgumentsDefinitionProviderInterface $provider): DefinitionBuilderInterface;

    /**
     * @return \Spotman\Defence\ArgumentDefinitionInterface[]
     */
    public function getArguments(): array;

    /**
     * @return bool
     */
    public function hasArguments(): bool;
}
