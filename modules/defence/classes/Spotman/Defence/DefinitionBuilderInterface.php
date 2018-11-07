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
     * Define string argument containing email
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function email(string $name): DefinitionBuilderInterface;

    /**
     * Define string argument containing HTML code
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function html(string $name): DefinitionBuilderInterface;

    /**
     * Define bool argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function bool(string $name): DefinitionBuilderInterface;

    /**
     * Define array argument
     *
     * @param string $name
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function array(string $name): DefinitionBuilderInterface;

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
     * @return ArgumentDefinitionInterface[]
     */
    public function getArguments(): array;

    /**
     * Rule helpers below
     */

    /**
     * @param int $min
     * @param int $max
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function countBetween(int $min, int $max): DefinitionBuilderInterface;

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function positive(): DefinitionBuilderInterface;

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
}
