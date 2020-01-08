<?php
namespace BetaKiller\Config;

use BetaKiller\Exception;

abstract class AbstractConfig
{
    /**
     * @var ConfigProviderInterface
     */
    private $config;

    /**
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     */
    public function __construct(ConfigProviderInterface $config)
    {
        $this->config = $config;
    }

    abstract protected function getConfigRootGroup(): string;

    /**
     * @param array      $path
     * @param bool|null $optional
     *
     * @return mixed|null
     */
    protected function get(array $path, bool $optional = null)
    {
        $configGroupName = $this->getConfigRootGroup();

        \array_unshift($path, $configGroupName);

        $value = $this->config->load($path);

        // empty() treats false as an empty value
        if (\is_bool($value)) {
            return $value;
        }

        if (empty($value) && !$optional) {
            throw new Exception('Missing ":key" config value', [
                ':key' => implode('.', $path),
            ]);
        }

        return $value;
    }
}
