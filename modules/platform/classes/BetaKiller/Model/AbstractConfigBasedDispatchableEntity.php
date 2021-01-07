<?php
namespace BetaKiller\Model;

use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Url\UrlPrototypeException;

abstract class AbstractConfigBasedDispatchableEntity implements ConfigBasedDispatchableEntityInterface
{
    /**
     * @var string
     */
    private $codename;

    /**
     * @var array
     */
    private $configOptions;

    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    final public static function getUrlContainerKey(): string
    {
        return static::getModelName();
    }

    /**
     * AbstractConfigBasedDispatchableEntity constructor.
     *
     * @param string     $codename
     * @param array|null $configOptions
     */
    public function __construct(string $codename, array $configOptions = null)
    {
        $this->codename      = $codename;
        $this->configOptions = $configOptions ?? [];
    }

    /**
     * Returns string identifier for current entity (DB record ID, instance-related unique hash, etc)
     *
     * @return string
     */
    public function getID(): string
    {
        // TODO Deal with this
        return $this->getCodename();
    }

    /**
     * @return bool
     */
    public function hasID(): bool
    {
        return (bool)$this->getID();
    }

    /**
     * Entity may return instances of linked entities if it have.
     * This method is used to fetch missing entities in UrlContainer walking through links between them
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getLinkedEntities(): array
    {
        // No entities by default
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getUrlParameterAccessAction(): ?string
    {
        // Use default one
        return null;
    }

    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getUrlKeyValue(string $key): string
    {
        $value = ($key === self::URL_KEY_CODENAME)
            ? $this->getCodename()
            : $this->getConfigOption($key);

        if (!$value) {
            throw new UrlPrototypeException('Config-based url parameter [:name] has no ":key" value', [
                ':name' => $this->getCodename(),
                ':key'  => $key,
            ]);
        }

        return $value;
    }

    /**
     * Config-based url parameters needs codename to be defined
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->codename;
    }

    /**
     * Config-based url parameters may define properties in config file
     *
     * @return array|null
     */
    public function getConfigOptions(): ?array
    {
        return $this->configOptions;
    }

    /**
     * Returns config-based property or null
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getConfigOption(string $key, $default = null)
    {
        return $this->configOptions[$key] ?? $default;
    }

    /**
     * Returns true if current parameter is the same as provided one
     *
     * @param \BetaKiller\Model\ConfigBasedDispatchableEntityInterface|mixed $parameter
     *
     * @return bool
     */
    public function isSameAs(UrlParameterInterface $parameter): bool
    {
        return ($parameter instanceof self)
            && ($parameter->getCodename() === $this->getCodename())
            && ($parameter->getConfigOptions() === $this->getConfigOptions());
    }

    /**
     * @inheritDoc
     */
    public function isCachingAllowed(): bool
    {
        // Allow caching for config-based Entities
        return true;
    }
}
