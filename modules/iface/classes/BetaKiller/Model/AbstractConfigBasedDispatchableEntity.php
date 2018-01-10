<?php
namespace BetaKiller\Model;

use BetaKiller\Url\UrlParameterInterface;
use BetaKiller\Url\UrlPrototypeException;

abstract class AbstractConfigBasedDispatchableEntity implements ConfigBasedDispatchableEntityInterface
{
    public const URL_KEY_CODENAME = 'codename';

    /**
     * @var string
     */
    private $codename;

    /**
     * @var array
     */
    private $options;

    /**
     * AbstractConfigBasedDispatchableEntity constructor.
     *
     * @param string     $codename
     * @param array|null $options
     */
    public function __construct(string $codename, ?array $options = null)
    {
        $this->codename = $codename;
        $this->options  = $options;
    }

    /**
     * Returns string identifier for current entity (DB record ID, instance-related unique hash, etc)
     *
     * @return string
     */
    public function getID(): string
    {
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
     * @return string
     */
    public function getModelName(): string
    {
        return $this->getCodename();
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
     * Returns true if this entity has linked one with provided key
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasLinkedEntity(string $key): bool
    {
        // No linked entities by default
        return false;
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
            : $this->getOption($key);

        if (!$value) {
            throw new UrlPrototypeException('Config-based url parameter [:name] has no ":key" value', [
                ':name'    => $this->getCodename(),
                ':key' => $key,
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
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * Returns config-based property or null
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
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
        return ($parameter::getUrlContainerKey() === $this::getUrlContainerKey())
            && ($parameter->getCodename() === $this->getCodename())
            && ($parameter->getOptions() === $this->getOptions());
    }
}
