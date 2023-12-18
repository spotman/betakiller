<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

final readonly class StackTraceItem
{
    public static function fromArray(array $data): self
    {
        return new self($data['file'], $data['line'], $data['class'] ?? null, $data['function']);
    }
    public function __construct(public string $file, public int $line, public ?string $class, public string $function)
    {
    }

    public function getCallee(): string
    {
        return !empty($this->class)
            ? $this->class.'::'.$this->function
            : $this->function;
    }
}
