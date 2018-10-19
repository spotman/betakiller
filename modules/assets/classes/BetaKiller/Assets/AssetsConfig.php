<?php
declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Config\AbstractConfig;

class AssetsConfig extends AbstractConfig
{
    public const CONFIG_KEY = 'assets';

    /**
     * Base assets url (with domain or without)
     */
    private const CONFIG_URL_PATH_KEY = 'url_path';

    /**
     * Allow deployment
     */
    private const CONFIG_DEPLOY_KEY = 'deploy';

    /**
     * Allow caching of actions content
     */
    private const CONFIG_CACHING_ENABLED_KEY = 'cache';

    /**
     * Nested group with models` definitions
     */
    public const CONFIG_MODELS_KEY = 'models';

    /**
     * Nested group with storages` defaults
     */
    public const CONFIG_STORAGES_KEY = 'storages';

    /**
     * Provider url key (slug)
     */
    public const CONFIG_MODEL_URL_KEY = 'url_key';

    /**
     * Model`s provider codename
     */
    public const CONFIG_MODEL_PROVIDER_KEY = 'provider';

    /**
     * Model`s path strategy codename
     */
    public const CONFIG_MODEL_PATH_STRATEGY_KEY = 'path_strategy';

    /**
     * Nested model`s storage config group
     */
    public const CONFIG_MODEL_STORAGE_KEY = 'storage';

    /**
     * Model`s storage codename
     */
    public const CONFIG_MODEL_STORAGE_NAME_KEY = 'name';

    /**
     * Model`s storage path name (single level)
     */
    public const CONFIG_MODEL_STORAGE_PATH_KEY = 'path';

    /**
     * Marker for setting model as "protected" (no direct public access)
     */
    private const CONFIG_MODEL_PROTECTED_KEY = 'protected';

    /**
     * Allowed mime-types
     */
    private const CONFIG_MODEL_MIMES = 'mimes';

    /**
     * Post upload handlers list
     */
    public const CONFIG_MODEL_POST_UPLOAD_KEY = 'post_upload';

    /**
     * Returns true if provider has protected content (no caching in public directory)
     *
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface $provider
     *
     * @return bool
     * @throws \BetaKiller\Exception
     */
    public function isProtected(AssetsProviderInterface $provider): bool
    {
        return (bool)$this->getProviderConfigValue($provider, [self::CONFIG_MODEL_PROTECTED_KEY]);
    }

    /**
     * Returns true if deployment to public directory is enabled
     *
     * @return bool
     * @throws \BetaKiller\Exception
     */
    public function isDeploymentEnabled(): bool
    {
        return (bool)$this->get([self::CONFIG_DEPLOY_KEY]);
    }

    /**
     * Returns true if caching of actions` data is allowed
     *
     * @return bool
     */
    public function isCachingEnabled(): bool
    {
        return (bool)$this->get([self::CONFIG_CACHING_ENABLED_KEY]);
    }

    public function getUrlKey(AssetsProviderInterface $provider): string
    {
        return $this->getUrlKeyConfigValue($provider) ?: $provider->getCodename();
    }

    /**
     * @return string
     */
    public function getUrlPath(): string
    {
        return (string)$this->get([self::CONFIG_URL_PATH_KEY]);
    }

    /**
     * Returns list of allowed MIME-types (or TRUE if all MIMEs are allowed)
     *
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface $provider
     *
     * @return array|bool
     * @throws \BetaKiller\Exception
     */
    public function getAllowedMimeTypes(AssetsProviderInterface $provider)
    {
        return $this->getProviderConfigValue($provider, [self::CONFIG_MODEL_MIMES]);
    }

    public function getModelStorage(string $modelName): string
    {
        return (string)$this->getModelConfigValue($modelName, [self::CONFIG_MODEL_PROVIDER_KEY]);
    }

    private function getUrlKeyConfigValue(AssetsProviderInterface $provider): string
    {
        return $this->getProviderConfigValue($provider, [self::CONFIG_MODEL_URL_KEY]);
    }

    protected function getConfigRootGroup(): string
    {
        return self::CONFIG_KEY;
    }

    /**
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface $provider
     * @param array                                               $path
     *
     * @return array|string|int|null
     * @throws \BetaKiller\Exception
     */
    public function getProviderConfigValue(AssetsProviderInterface $provider, array $path)
    {
        return $this->getModelConfigValue($provider->getCodename(), $path);
    }

    /**
     * @param string $modelName
     * @param array  $path
     *
     * @return array|string|int|null
     * @throws \BetaKiller\Exception
     */
    private function getModelConfigValue(string $modelName, array $path)
    {
        \array_unshift($path, $modelName, self::CONFIG_MODELS_KEY);

        return $this->get($path);
    }
}
