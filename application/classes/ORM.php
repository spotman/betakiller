<?php

use BetaKiller\DI\Container;
use BetaKiller\Factory\OrmFactory;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Search\SearchResultsInterface;
use BetaKiller\Url\Parameter\UrlParameterException;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Utils;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use ORM\PaginateHelper;

class ORM extends Utils\Kohana\ORM implements ExtendedOrmInterface
{
    /**
     * @var OrmFactory
     */
    private static $factoryInstance;

    /**
     * @param string         $model
     * @param int|array|null $id
     *
     * @return OrmInterface|mixed
     */
    public static function factory($model, $id = null)
    {
        // Coz ORM do not cares about letter cases
        $model = str_replace(' ', '_', ucwords(str_replace('_', ' ', $model)));

        // No direct search by ID coz ORM crashes with circular dependencies when extended from TreeModel and initialized with id

        /** @var OrmInterface $object */
        $object = self::getFactory()->create($model);

        // Old Kohana sugar for searching in model
        if (is_array($id)) {
            foreach ((array)$id as $column => $value) {
                // Passing an array of column => values
                $object->where($column, '=', $value);
            }

            return $object->find();
        }

        // Search by ID
        if ($id) {
            return $object->get_by_id($id, true); // Allow missing elements for BC
        }

        // Plain model factory
        return $object;
    }

    protected static function getFactory(): OrmFactory
    {
        if (!self::$factoryInstance) {
            /** @var OrmFactory $factory */
            $factory = Container::getInstance()->get(OrmFactory::class);

            self::$factoryInstance = $factory;
        }

        return self::$factoryInstance;
    }

    public function getModelName(OrmInterface $object = null): string
    {
        return static::detectModelName($object);
    }

    protected static function detectModelName(OrmInterface $object = null): string
    {
        $className = $object ? get_class($object) : static::class;

        // Try namespaces first
        $pos = strrpos($className, '\\');

        if ($pos === false) {
            // Use legacy naming
            $pos = 5; // "Model_" is 6 letters
        }

        return substr($className, $pos + 1);
    }

    /**
     * @param string $alias
     *
     * @return AbstractEntityInterface
     */
    protected function getRelatedEntity(string $alias): AbstractEntityInterface
    {
        $entity = $this->get($alias);
        if (!($entity instanceof AbstractEntityInterface)) {
            throw new \RuntimeException(
                sprintf('Unable get related entity by alias "%s"', $alias)
            );
        }
        if (!$this->loaded() || !$entity->loaded()) {
            throw new \RuntimeException(
                sprintf('Related entity by alias "%s" not loaded', $alias)
            );
        }

        return $entity;
    }

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->configure();

        parent::_initialize();
    }

    protected function configure(): void
    {
        // Empty by default
    }

    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string
    {
        return static::detectModelName();
    }

    /**
     * Returns true if current parameter is the same as provided one
     *
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface $parameter
     *
     * @return bool
     * @throws \BetaKiller\Url\Parameter\UrlParameterException
     */
    public function isSameAs(UrlParameterInterface $parameter): bool
    {
        if (!($parameter instanceof static)) {
            throw new UrlParameterException('Trying to compare instances of different classes');
        }

        return $parameter->getID() === $this->getID();
    }

    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     */
    public function getUrlKeyValue(string $key): string
    {
        return (string)$this->get($key);
    }

    /**
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getLinkedEntities(): array
    {
        $entities = [];

        foreach ($this->_belongs_to as $column => $config) {
            /** @var OrmInterface $model */
            $model = $this->get($column);

            if ($model->loaded() && $model->get_id()) {
                $entities[] = $model;
            }
        }

        return $entities;
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
        foreach ($this->_belongs_to as $column => $config) {
            $modelName = $config['model'] ?? null;

            if ($modelName && $modelName === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns string identifier of current entity
     *
     * @return string
     */
    public function getID(): string
    {
        return (string)$this->pk();
    }

    public function hasID(): bool
    {
        return (bool)$this->pk();
    }

    /**
     * @param int      $page
     * @param int|null $itemsPerPage
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    public function getSearchResults(int $page, ?int $itemsPerPage = null): SearchResultsInterface
    {
        // Оборачиваем в пэйджинатор
        $pager = $this->paginateHelper($page, $itemsPerPage);

        // Получаем результаты поиска
        $items = $pager->getResults();

        // Оборачиваем в контейнер
        $results = \BetaKiller\Search\SearchResults::factory(
            $pager->getTotalItems(),
            $pager->getTotalPages(),
            $pager->hasNextPage()
        );

        // Добавляем элементы
        foreach ($items as $item) {
            $results->addItem($item);
        }

        return $results;
    }

    /**
     * @return array
     */
    public function getSearchResultsItemData(): array
    {
        return $this->as_array();
    }

    /**
     * @param int      $currentPage
     * @param int|null $itemsPerPage
     *
     * @return \ORM\PaginateHelper
     */
    protected function paginateHelper(int $currentPage, ?int $itemsPerPage = null): PaginateHelper
    {
        return \ORM\PaginateHelper::create(
            $this,
            $currentPage,
            $itemsPerPage ?: 25
        );
    }
}
