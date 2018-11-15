<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ConfigBasedDispatchableEntityInterface;
use BetaKiller\Model\WebHook;
use BetaKiller\Model\WebHookModelInterface;

/**
 * Class WebHookRepository
 *
 * @package BetaKiller\Repository
 * @method WebHookModelInterface[] getAll()
 * @method WebHookModelInterface[] findItemByUrlKeyValue()
 */
class WebHookRepository extends AbstractConfigBasedDispatchableRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return WebHookModelInterface::URL_KEY_CODENAME;
    }

    public function getByServiceName(string $serviceName): array
    {
        return \array_filter($this->getAll(), function (WebHookModelInterface $model) use ($serviceName) {
            return $model->getServiceName() === $serviceName;
        });
    }

    /**
     * @param mixed[] $config
     */
    protected function fillFromConfig(array $config): void
    {
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
            $this->addItem($webHookCodename, [
                WebHook::OPTION_SERVICE_NAME  => $serviceName,
                WebHook::OPTION_SERVICE_EVENT => $eventName,
            ]);
        }
    }

    /**
     * @param string     $codename
     * @param array|null $properties
     *
     * @return ConfigBasedDispatchableEntityInterface
     */
    protected function createItemFromCodename(
        string $codename,
        ?array $properties = null
    ): ConfigBasedDispatchableEntityInterface {
        return new WebHook($codename, $properties);
    }

    protected function getItemsListConfigKey(): array
    {
        return ['webhooks'];
    }
}
