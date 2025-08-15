<?php

declare(strict_types=1);

namespace BetaKiller\Helper;

use Cherif\InertiaPsr15\Model\LazyProp;
use Cherif\InertiaPsr15\Service\Inertia;

final class InertiaPropsBuilder
{
    private array $props = [];

    /**
     * Add prop from a variable which is defined already
     *
     * @param string                $name
     * @param string|int|bool|array $value
     *
     * @return $this
     */
    public function regular(string $name, string|int|bool|array $value): self
    {
        return $this->add($name, $value);
    }

    /**
     * Add prop which will be resolved if needed
     *
     * @param string   $name
     * @param callable $fn
     *
     * @return $this
     */
    public function deferred(string $name, callable $fn): self
    {
        return $this->add($name, $fn);
    }

    /**
     * Add prop which will be added only if requested explicitly
     *
     * @param string   $name
     * @param callable $fn
     *
     * @return $this
     */
    public function optional(string $name, callable $fn): self
    {
        return $this->add($name, Inertia::lazy($fn));
    }

    public function getAll(): array
    {
        return $this->props;
    }

    private function add(string $name, string|int|bool|array|callable|LazyProp $value): self
    {
        $this->props[$name] = $value;

        return $this;
    }
}
