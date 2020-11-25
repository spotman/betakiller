<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Parameter\ArgumentParameterInterface;

interface ArgumentsInterface
{
    public const IDENTITY_KEY = 'id';

    /**
     * @return string
     */
    public function getID(): string;

    /**
     * Returns true if current arguments set contains identity value
     *
     * @return bool
     */
    public function hasID(): bool;

    /**
     * Returns true if current arguments set contains non-null value for provided key
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Returns true if provided key value is null
     *
     * @param string $key
     *
     * @return bool
     */
    public function isNull(string $key): bool;

    /**
     * @param string $key
     *
     * @throws \LogicException
     */
    public function mustHave(string $key): void;

    /**
     * @param string $key
     *
     * @return int
     */
    public function getInt(string $key): int;

    /**
     * @param string $key
     *
     * @return float
     */
    public function getFloat(string $key): float;

    /**
     * @param string $key
     *
     * @return string
     */
    public function getString(string $key): string;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function getBool(string $key): bool;

    /**
     * @param string $key
     *
     * @return \DateTimeImmutable
     */
    public function getDateTime(string $key): \DateTimeImmutable;

    /**
     * @param string $key
     *
     * @return \Spotman\Defence\Parameter\ArgumentParameterInterface|mixed
     */
    public function getParam(string $key): ArgumentParameterInterface;

    /**
     * @param string $key
     *
     * @return array
     */
    public function getArray(string $key): array;

    /**
     * @return mixed[]
     */
    public function getAll(): array;
}
