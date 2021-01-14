<?php
namespace BetaKiller\Repository;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model\ConfigBasedDispatchableEntityInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;

abstract class AbstractConfigBasedDispatchableRepository extends AbstractReadOnlyRepository
    implements ConfigBasedDispatchableRepositoryInterface
{
    /**
     * @var ConfigBasedDispatchableEntityInterface[]
     */
    private $items = [];

    /**
     * ParserRepository constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $configProvider
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function __construct(ConfigProviderInterface $configProvider)
    {
        $configKey = $this->getItemsListConfigKey();
        $config    = (array)$configProvider->load($configKey);

        if (!$config) {
            throw new RepositoryException('Empty items list config for :repo repository', [
                ':repo' => static::getCodename(),
            ]);
        }

        $this->fillFromConfig($config);
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function findById(string $id)
    {
        // Codename is ID
        return $this->findByCodename($id);
    }

    /**
     * @param string $id
     *
     * @return mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getById(string $id)
    {
        // Codename is ID
        return $this->getByCodename($id);
    }

    /**
     * @param string $name
     *
     * @return ConfigBasedDispatchableEntityInterface|mixed
     */
    public function findByCodename(string $name)
    {
        $name = $this->prepareCodename($name);

        return $this->items[$name] ?? null;
    }

    /**
     * @param string $name
     *
     * @return ConfigBasedDispatchableEntityInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByCodename(string $name)
    {
        $item = $this->findByCodename($name);

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
     * @param int|null $currentPage
     * @param int|null $itemsPerPage
     *
     * @return \BetaKiller\Model\AbstractEntityInterface[]
     */
    public function getAllPaginated(int $currentPage, int $itemsPerPage): array
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     */
    public function getAllAvailableItems(): array
    {
        return $this->getAll();
    }

    /**
     * @param mixed[] $config
     */
    protected function fillFromConfig(array $config): void
    {
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

            $this->addItem($codename, $options);
        }
    }

    protected function addItem(string $codename, ?array $options = null): void
    {
        $this->items[$codename] = $this->createItemFromCodename($codename, $options);
    }

    /**
     * Performs search for model item where the url key property is equal to $value
     *
     * @param string                                          $value
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return ConfigBasedDispatchableEntityInterface
     */
    public function findItemByUrlKeyValue(string $value, UrlContainerInterface $params): ?UrlParameterInterface
    {
        $key = $this->getUrlKeyName();

        if ($key === ConfigBasedDispatchableEntityInterface::URL_KEY_CODENAME) {
            return $this->getByCodename($value);
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
     * @return \BetaKiller\Model\ConfigBasedDispatchableEntityInterface|mixed|null
     */
    protected function findOneByOptionValue(string $name, string $value)
    {
        foreach ($this->items as $item) {
            if ($item->getConfigOption($name) === $value) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return \BetaKiller\Model\ConfigBasedDispatchableEntityInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function getOneByOptionValue(string $name, string $value)
    {
        $item = $this->findOneByOptionValue($name, $value);

        if (!$item) {
            throw new RepositoryException('Can not find item by :name = :value in repository :repo', [
                ':name'  => $name,
                ':value' => $value,
                ':repo'  => static::getCodename(),
            ]);
        }

        return $item;
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

    abstract protected function prepareCodename(string $codename): string;

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
