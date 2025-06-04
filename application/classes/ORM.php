<?php

use BetaKiller\DI\Container;
use BetaKiller\Factory\OrmFactory;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Url\Parameter\UrlParameterException;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Utils;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

abstract class ORM extends Utils\Kohana\ORM implements ExtendedOrmInterface
{
    /**
     * @var \OrmFactoryInterface|null
     */
    private static ?OrmFactoryInterface $factoryInstance = null;

    public static function setModelFactory(OrmFactoryInterface $factory): void
    {
        self::$factoryInstance = $factory;
    }

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

    protected static function getFactory(): OrmFactoryInterface
    {
        if (!self::$factoryInstance) {
            throw new LogicException('Set OrmFactoryInterface instance before using ORM');
        }

        return self::$factoryInstance;
    }

    public static function getModelName(): string
    {
        return static::detectModelName();
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
     * @param string    $alias
     * @param bool|null $isNullable
     *
     * @return mixed
     */
    protected function getRelatedEntity(string $alias, bool $isNullable = null)
    {
        $entity = $this->getRelation($alias);

        if (!$entity->loaded()) {
            if ($isNullable) {
                return null;
            }

            if (!$this->loaded()) {
                throw new \RuntimeException(
                    sprintf('Entity "%s" is not loaded', self::detectModelName($this))
                );
            }

            throw new \RuntimeException(
                sprintf('Related alias "%s" is not loaded in entity "%s" with ID "%s"',
                    $alias,
                    self::detectModelName($this),
                    $this->pk()
                )
            );
        }

        return $entity;
    }

    protected function hasRelatedEntity(string $alias): bool
    {
        return $this->getRelation($alias)->loaded();
    }

    /**
     * @param string $name
     *
     * @return AbstractEntityInterface|ExtendedOrmInterface|mixed
     */
    protected function getRelation(string $name)
    {
        $relation = $this->get($name);

        if (!($relation instanceof AbstractEntityInterface)) {
            throw new \RuntimeException(
                sprintf('Can not get related entity by alias "%s" from entity "%s"', $name, $this->object_name())
            );
        }

        return $relation;
    }

    /**
     * @param string $name
     *
     * @return OrmInterface[]|AbstractEntityInterface[]|mixed[]
     */
    protected function getAllRelated(string $name): array
    {
        // Return preloaded data
        if (array_key_exists($name, $this->_related_has_many)) {
            return $this->_related_has_many[$name];
        }

        return $this->getRelation($name)->get_all();
    }

    protected function countAllRelated(string $name): int
    {
        return $this->getRelation($name)->count_all();
    }

    protected function hasRelatedRecords(string $name): bool
    {
        return $this->countAllRelated($name) > 0;
    }

    /**
     * @param string                                           $name
     * @param OrmInterface[]|AbstractEntityInterface[]|mixed[] $newModels
     *
     * @return bool Returns true if dataset is changed
     */
    protected function mergeRelatedModels(string $name, array $newModels): bool
    {
        $isChanged = false;

        // Get old models
        $oldModels = $this->getAllRelated($name);

        // Remove unused first to prevent FK warnings
        foreach ($oldModels as $old) {
            if (!$this->findModelInList($old, $newModels)) {
                $this->removeRelated($name, $old);
                $isChanged = true;
            }
        }

        // Add absent last
        foreach ($newModels as $new) {
            $existing = $this->findModelInList($new, $oldModels);
            if (!$existing) {
                $this->addRelated($name, $new);
                $isChanged = true;
            } else {
                // Import data from new model into existing one (keep primary key of existing model)
                $existing->values($new->as_array());

                $isChanged = $isChanged || $existing->changed();

                // Save existing model
                $existing->save();
            }
        }

        return $isChanged;
    }

    /**
     * @param OrmInterface                                $model
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface[] $list
     *
     * @return OrmInterface|null
     */
    private function findModelInList(OrmInterface $model, array $list): ?OrmInterface
    {
        foreach ($list as $item) {
            if ($item->isEqualTo($model)) {
                return $item;
            }
        }

        return null;
    }

    protected function addRelated(string $relationName, $model): void
    {
        if (!$model instanceof OrmInterface) {
            throw new LogicException(
                sprintf('Model %s must implement OrmInterface for using ORM::removeRalted()', get_class($model))
            );
        }

        if (isset($this->_belongs_to[$relationName])) {
            $foreignKey = $this->_belongs_to[$relationName]['foreign_key'];

            // Set internal column
            $this->set($foreignKey, $model->pk());
            $this->save();
        } elseif (isset($this->_has_one[$relationName])) {
            $foreignKey = $this->_has_one[$relationName]['foreign_key'];

            // External column
            $model->set($foreignKey, $this->getID());
            $model->save();
        } elseif (isset($this->_has_many[$relationName]['through'])) {
            // Has_many "through" relationship
            $this->add($relationName, $model);
        } elseif (isset($this->_has_many[$relationName])) {
            // Simple has_many relationship, target model's foreign key is this model's primary key
            $foreignKey = $this->_has_many[$relationName]['foreign_key'];

            // External column
            $model->set($foreignKey, $this->getID());
            $model->save();

            // Clear cached data (force DB query on next fetch)
            $this->resetHasManyData($relationName);
        } else {
            throw new \Kohana_Exception('The related alias ":property" does not exist in the :class class', [
                ':property' => $relationName,
                ':class'    => get_class($this),
            ]);
        }
    }

    protected function removeRelated(string $relationName, $model): void
    {
        if (!$model instanceof OrmInterface) {
            throw new LogicException(
                sprintf('Model %s must implement OrmInterface for using ORM::removeRelated()', get_class($model))
            );
        }

        if (isset($this->_belongs_to[$relationName])) {
            $model->delete(); // $this model may be deleted by SQL constraints after that
        } elseif (isset($this->_has_one[$relationName])) {
            $model->delete();
        } elseif (isset($this->_has_many[$relationName]['through'])) {
            // Has_many "through" relationship
            $this->remove($relationName, $model);
        } elseif (isset($this->_has_many[$relationName])) {
            // Simple has_many relationship, target model's foreign key is this model's primary key
            $model->delete();
        } else {
            throw new \Kohana_Exception('The related alias ":property" does not exist in the :class class', [
                ':property' => $relationName,
                ':class'    => get_class($this),
            ]);
        }
    }

    protected function hasRelated(string $relationName, $model): bool
    {
        if (!$model instanceof OrmInterface) {
            throw new LogicException(
                sprintf('Model %s must implement OrmInterface for using ORM::hasRelated()', get_class($model))
            );
        }

        if (isset($this->_belongs_to[$relationName]) || isset($this->_has_one[$relationName])) {
            $related = $this->getRelatedEntity($relationName, true);

            return $related && $related->isEqualTo($model);
        }

        // Has_many "through" relationship
        if (isset($this->_has_many[$relationName]['through'])) {
            return $this->has($relationName, $model);
        }

        // Simple has_many relationship, target model's foreign key is this model's primary key
        if (isset($this->_has_many[$relationName])) {
            return (bool)$this->getRelation($relationName)
                ->where($relationName.self::COL_SEP.$model->primary_key(), '=', $model->pk())
                ->find();
        }

        throw new \Kohana_Exception('The related alias ":property" does not exist in the :class class', [
            ':property' => $relationName,
            ':class'    => get_class($this),
        ]);
    }

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     * @throws Exception
     */
    protected function _initialize()
    {
        $this->configure();

        parent::_initialize();
    }

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    abstract protected function configure(): void;

    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string
    {
        return static::getModelName();
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

            if ($model->loaded() && $model->pk()) {
                $entities[] = $model;
            }
        }

        return $entities;
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

    protected function setOnce(string $key, $value): void
    {
        if ($this->hasKeyValue($key)) {
            throw new LogicException(
                sprintf('Can not reassign key "%s" in "%s" model with ID "%s"',
                    $key,
                    static::getModelName(),
                    $this->pk()
                )
            );
        }

        $this->set($key, $value);
    }

    private function hasKeyValue(string $key): bool
    {
        $current = $this->get($key);

        switch (true) {
            case $current instanceof self:
                return $current->loaded();

            case is_scalar($current):
                return (bool)$current;

            default:
                return !empty($current);
        }
    }

    /**
     * @inheritDoc
     */
    public function isCachingAllowed(): bool
    {
        // Caching for Entities is allowed by default
        return true;
    }
}
