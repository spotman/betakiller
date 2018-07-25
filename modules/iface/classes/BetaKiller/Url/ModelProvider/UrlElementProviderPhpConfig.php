<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Url\WebHookModelInterface;
use BetaKiller\Url\ZoneInterface;

class UrlElementProviderPhpConfig implements UrlElementProviderInterface
{
    private const CONFIG_GROUP_WEBHOOK = 'webhooks';

    /**
     * @var AbstractPlainUrlElementModel[]
     */
    private $models = [];

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $configProvider;

    /**
     * UrlElementProviderPhpConfig constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $configProvider
     */
    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getAll(): array
    {
        if (!$this->models) {
            $this->loadAll();
        }

        return $this->models;
    }

    private function loadAll(): void
    {
        $config = (array)$this->configProvider->load([self::CONFIG_GROUP_WEBHOOK]);

        if (!$config) {
            return;
        }

        foreach ($config as $serviceName => $serviceWebHooks) {
            $this->loadServiceWebhooks($serviceName, $serviceWebHooks);
        }
    }

    /**
     * @param string $serviceName
     * @param array  $serviceWebHooks
     */
    private function loadServiceWebhooks(string $serviceName, array $serviceWebHooks): void
    {
        foreach ($serviceWebHooks as $eventName => $webHookConfig) {
            $this->models[] = $this->createWebHookModel($serviceName, $eventName, $webHookConfig);
        }
    }

    private function createWebHookModel(string $service, string $event, array $config): WebHookModelInterface
    {
        $model = new WebHookPlainModel;

        $model->fromArray([
            $model::OPTION_CODENAME      => $config['name'],
            $model::OPTION_URI           => 'wh-'.\lcfirst($service).'-'.\lcfirst($event),
            $model::OPTION_SERVICE_NAME  => $service,
            $model::OPTION_SERVICE_EVENT => $event,
        ]);

        return $model;
    }
}
