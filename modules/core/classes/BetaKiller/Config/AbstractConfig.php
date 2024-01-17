<?php
namespace BetaKiller\Config;

use BetaKiller\Exception;
use function array_unshift;
use function is_bool;

abstract class AbstractConfig
{
    private const TYPE_ARRAY  = 'array';
    private const TYPE_STRING = 'string';
    private const TYPE_INT    = 'int';
    private const TYPE_BOOL   = 'bool';

    /**
     * @var ConfigProviderInterface
     */
    private ConfigProviderInterface $config;

    /**
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     */
    public function __construct(ConfigProviderInterface $config)
    {
        $this->config = $config;
    }

    abstract protected function getConfigRootGroup(): string;

    /**
     * @param array     $path
     * @param bool|null $optional
     *
     * @return array|string|int|bool|null
     * @throws \BetaKiller\Exception
     */
    protected function get(array $path, bool $optional = null): array|string|int|bool|null
    {
        $value = $this->config->load($this->getConfigRootGroup(), $path);

        // empty() treats false as an empty value
        if (is_bool($value)) {
            return $value;
        }

        if (empty($value) && !$optional) {
            throw new Exception('Missing ":key" config value', [
                ':key' => implode('.', $path),
            ]);
        }

        return $value;
    }

    /**
     * @param array     $path
     * @param bool|null $optional
     *
     * @return array
     * @throws \BetaKiller\Exception
     */
    protected function getArray(array $path, bool $optional = null): array
    {
        return $this->getTypedValue(self::TYPE_ARRAY, $path, $optional);
    }

    /**
     * @param array     $path
     * @param bool|null $optional
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    protected function getString(array $path, bool $optional = null): string
    {
        return $this->getTypedValue(self::TYPE_STRING, $path, $optional);
    }

    /**
     * @param array     $path
     * @param bool|null $optional
     *
     * @return int
     * @throws \BetaKiller\Exception
     */
    protected function getInt(array $path, bool $optional = null): int
    {
        return $this->getTypedValue(self::TYPE_INT, $path, $optional);
    }

    /**
     * @param array     $path
     * @param bool|null $optional
     *
     * @return bool
     * @throws \BetaKiller\Exception
     */
    protected function getBool(array $path, bool $optional = null): bool
    {
        return $this->getTypedValue(self::TYPE_BOOL, $path, $optional);
    }

    /**
     * @throws \BetaKiller\Exception
     */
    private function getTypedValue(string $type, array $path, bool $optional = null): array|string|int|bool|null
    {
        $value = $this->get($path, $optional);

        if (is_null($value)) {
            return null;
        }

        $checks = [
            self::TYPE_ARRAY  => fn($value) => is_array($value),
            self::TYPE_STRING => fn($value) => is_string($value),
            self::TYPE_INT    => fn($value) => is_int($value),
            self::TYPE_BOOL   => fn($value) => is_bool($value),
        ];

        $checkHandler = $checks[$type] ?? null;

        if (!$checkHandler) {
            throw new Exception('Unknown Config data type ":type" for path ":path"', [
                ':type' => $type,
                ':path' => implode('.', $path),
            ]);
        }

        if (!$checkHandler($value)) {
            throw new Exception('Config value at ":path" must be type of ":type"', [
                ':type' => $type,
                ':path' => implode('.', $path),
            ]);
        }

        return $value;
    }
}
