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
     * @param string|array $path
     * @param null         $default
     *
     * @return array|\BetaKiller\Config\ConfigGroupInterface|null|string
     */
    protected function get(array $path, $default = null)
    {
        $configGroupName = $this->getConfigRootGroup();

        return $this->config->load(array_merge([$configGroupName], $path)) ?: $default;
    }
}
