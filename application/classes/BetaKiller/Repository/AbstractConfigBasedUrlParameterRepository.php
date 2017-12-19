<?php
namespace BetaKiller\Repository;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\IFace\Url\ConfigBasedUrlParameterInterface;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\IFace\Url\UrlParameterInterface;

abstract class AbstractConfigBasedUrlParameterRepository extends AbstractUrlParameterRepository
{
    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    protected $configProvider;

    /**
     * @var mixed[]
     */
    protected $items = [];

    /**
     * ParserRepository constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $configProvider
     */
    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        $this->fillFromConfig();
    }

    /**
     * @param string $name
     *
     * @return UrlParameterInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByCodename(string $name)
    {
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
     * Performs search for model item where the url key property is equal to $value
     *
     * @param string                                      $value
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $params
     *
     * @return \BetaKiller\IFace\Url\UrlParameterInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findItemByUrlKeyValue(string $value, UrlContainerInterface $params): UrlParameterInterface
    {
        $value = ucfirst($value);

        return $this->findByCodename($value);
    }

    /**
     * Returns list of available items (model records) by url key property
     *
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $parameters
     *
     * @return \BetaKiller\IFace\Url\UrlParameterInterface[]
     */
    public function getItemsHavingUrlKey(UrlContainerInterface $parameters): array
    {
        // No filtering by parameters here
        return $this->getAll();
    }

    /**
     * @return \Traversable|ConfigBasedUrlParameterInterface[]|mixed[]
     */
    public function getAll()
    {
        return $this->items;
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findById(int $id)
    {
        throw new RepositoryException('Config based :repo repository can not find parameter by id', [
            ':repo' => static::getCodename(),
        ]);
    }

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
                $options = null;
            }

            $this->items[$codename] = $this->createItemFromCodename($codename, $options);
        }
    }

    abstract protected function getItemsListConfigKey(): array;

    /**
     * @param string     $codename
     * @param array|null $properties
     *
     * @return ConfigBasedUrlParameterInterface|mixed
     */
    abstract protected function createItemFromCodename(string $codename, ?array $properties = null): ConfigBasedUrlParameterInterface;
}
