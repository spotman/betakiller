<?php
namespace BetaKiller\Repository;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Model\ConfigBasedDispatchableEntityInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;

abstract class AbstractConfigBasedDispatchableRepository extends AbstractPredefinedRepository
    implements DispatchableRepositoryInterface
{
    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    protected $configProvider;

    /**
     * @var ConfigBasedDispatchableEntityInterface[]
     */
    protected $items = [];

    /**
     * ParserRepository constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $configProvider
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        $this->fillFromConfig();
    }

    /**
     * @param string $id
     *
     * @return mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findById(string $id)
    {
        // Codename is ID
        return $this->findByCodename($id);
    }

    /**
     * @param string $name
     *
     * @return ConfigBasedDispatchableEntityInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByCodename(string $name)
    {
        $name = ucfirst($name);

        $item = $this->items[$name] ?? null;

        if (!$item) {
            throw new RepositoryException('Can not find item [:name] in repository :repo', [
                ':name' => $name,
                ':repo' => static::getCodename(),
            ]);
        }

        return $item;
    }

    /**
     * @return ConfigBasedDispatchableEntityInterface[]|mixed[]
     */
    public function getAll(): array
    {
        return $this->items;
    }

    /**
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function fillFromConfig(): void
    {
        $configKey = $this->getItemsListConfigKey();
        $config    = $this->configProvider->load($configKey);

        if (!$config) {
            throw new RepositoryException('Empty items list config for :repo repository', [
                ':repo' => static::getCodename(),
            ]);
        }

        // Filling repository with instances by config
        foreach ($config as $name => $options) {
            if (\is_string($name)) {
                // Full definition with properties
                $codename = $name;
            } else {
                // Simple definition without properties
                $codename = $options;
                $options  = null;
            }

            $this->items[$codename] = $this->createItemFromCodename($codename, $options);
        }
    }

    /**
     * Performs search for model item where the url key property is equal to $value
     *
     * @param string                                          $value
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return ConfigBasedDispatchableEntityInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findItemByUrlKeyValue(string $value, UrlContainerInterface $params): UrlParameterInterface
    {
        $key = $this->getUrlKeyName();

        if ($key === ConfigBasedDispatchableEntityInterface::URL_KEY_CODENAME) {
            return $this->findByCodename($value);
        }

        return $this->findOneByOptionValue($key, $value);
    }

    /**
     * Returns list of available items (model records) by url key property
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $parameters
     *
     * @return ConfigBasedDispatchableEntityInterface[]
     */
    public function getItemsHavingUrlKey(UrlContainerInterface $parameters): array
    {
        // No filtering by parameters here
        return $this->getAll();
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return \BetaKiller\Model\ConfigBasedDispatchableEntityInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function findOneByOptionValue(string $name, string $value)
    {
        foreach ($this->items as $item) {
            if ($item->getConfigOption($name) === $value) {
                return $item;
            }
        }

        throw new RepositoryException('Can not find item by :name = :value in repository :repo', [
            ':name'  => $name,
            ':value' => $value,
            ':repo'  => static::getCodename(),
        ]);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return \BetaKiller\Model\ConfigBasedDispatchableEntityInterface[]|array
     */
    protected function findAllByOptionValue(string $name, string $value): array
    {
        $filtered = [];

        foreach ($this->items as $item) {
            if ($item->getConfigOption($name) === $value) {
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    /**
     * @param string     $codename
     * @param array|null $properties
     *
     * @return ConfigBasedDispatchableEntityInterface
     */
    abstract protected function createItemFromCodename(
        string $codename,
        ?array $properties = null
    ): ConfigBasedDispatchableEntityInterface;

    abstract protected function getItemsListConfigKey(): array;
}
