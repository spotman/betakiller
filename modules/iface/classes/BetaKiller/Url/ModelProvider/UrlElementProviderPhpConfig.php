<?php
declare(strict_types=1);

namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Url\WebHookModelInterface;

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
        foreach ($serviceWebHooks as $eventName => $webHookCodename) {
            $this->models[] = $this->createWebHookModel($serviceName, $eventName, $webHookCodename);
        }
    }

    private function createWebHookModel(string $service, string $event, string $codename): WebHookModelInterface
    {
        $model = new WebHookPlainModel;

        $model->fromArray([
            $model::OPTION_CODENAME      => $codename,
            $model::OPTION_URI           => \mb_strtolower('wh-'.$service.'-'.$event),
            $model::OPTION_SERVICE_NAME  => $service,
            $model::OPTION_SERVICE_EVENT => $event,
        ]);

        return $model;
    }
}
