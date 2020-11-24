<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Parameter\ParameterInterface;

class Arguments implements ArgumentsInterface
{
    /**
     * @var array
     */
    private array $args;

    /**
     * Arguments constructor.
     *
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->args = $array;
    }

    public function getID(): string
    {
        $id = $this->detectID();

        if (!$id) {
            throw new \InvalidArgumentException('Missing identity value');
        }

        return $id;
    }

    /**
     * Returns true if current arguments set contains identity value
     *
     * @return bool
     */
    public function hasID(): bool
    {
        return (bool)$this->detectID();
    }

    /**
     * Returns true if current arguments set contains non-null value for provided key
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        // Check for non-null value also to simplify client logic
        return isset($this->args[$key]);
    }

    /**
     * Returns true if provided key value is null
     *
     * @param string $key
     *
     * @return bool
     */
    public function isNull(string $key): bool
    {
        return \array_key_exists($key, $this->args) && $this->args[$key] === null;
    }

    /**
     * @inheritDoc
     */
    public function mustHave(string $key): void
    {
        if (!$this->has($key)) {
            throw new \LogicException(sprintf('Missing required key "%s"', $key));
        }
    }

    /**
     * @param string $key
     *
     * @return int
     */
    public function getInt(string $key): int
    {
        return (int)$this->args[$key];
    }

    /**
     * @param string $key
     *
     * @return float
     */
    public function getFloat(string $key): float
    {
        return (float)$this->args[$key];
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getString(string $key): string
    {
        return (string)$this->args[$key];
    }

    /**
     * @param string $key
     *
     * @return \DateTimeImmutable
     */
    public function getDateTime(string $key): \DateTimeImmutable
    {
        return $this->args[$key];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function getBool(string $key): bool
    {
        return (bool)$this->args[$key];
    }

    /**
     * @inheritDoc
     */
    public function getParam(string $key): ParameterInterface
    {
        return $this->args[$key];
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function getArray(string $key): array
    {
        return (array)$this->args[$key];
    }

    /**
     * @return mixed[]
     */
    public function getAll(): array
    {
        return $this->args;
    }

    private function detectID(): ?string
    {
        if (!empty($this->args[self::IDENTITY_KEY])) {
            return (string)$this->args[self::IDENTITY_KEY];
        }

        $first = \reset($this->args);

        if (\is_array($first) && !empty($first[self::IDENTITY_KEY])) {
            return (string)$first[self::IDENTITY_KEY];
        }

        return null;
    }
}
