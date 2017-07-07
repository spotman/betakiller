<?php
namespace BetaKiller\Repository;

use BetaKiller\Config\ConfigProviderInterface;
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
     */
    public function findItemByUrlKeyValue(string $value, UrlContainerInterface $params): ?UrlParameterInterface
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
     * @return \Traversable|mixed[]
     */
    public function getAll()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @param int $id
     *
     * @return mixed
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
        foreach ($config as $codename) {
            $this->items[$codename] = $this->createItemFromCodename($codename);
        }
    }

    abstract protected function getItemsListConfigKey(): array;

    /**
     * @param string $codename
     *
     * @return mixed
     */
    abstract protected function createItemFromCodename(string $codename);
}
