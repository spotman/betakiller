<?php
namespace BetaKiller\Config;


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
     * @param mixed|null $default
     *
     * @return array|string|bool|null
     */
    protected function get(array $path, $default = null)
    {
        $configGroupName = $this->getConfigRootGroup();

        return $this->config->load(array_merge([$configGroupName], $path)) ?: $default;
    }
}
