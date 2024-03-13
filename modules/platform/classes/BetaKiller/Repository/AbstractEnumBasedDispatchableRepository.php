<?php
namespace BetaKiller\Repository;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model\EnumBasedDispatchableEntityInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlPrototype;

abstract class AbstractEnumBasedDispatchableRepository extends AbstractReadOnlyRepository
    implements DispatchableRepositoryInterface
{
    /**
     * @var EnumBasedDispatchableEntityInterface[]
     */
    private array $items;

    /**
     * AbstractEnumBasedDispatchableRepository constructor.
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function __construct()
    {
        $this->items = $this->getItems();

        if (!$this->items) {
            throw new RepositoryException('Empty items for :repo repository', [
                ':repo' => static::getCodename(),
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function findById(string $id): ?EnumBasedDispatchableEntityInterface
    {
        foreach ($this->items as $item) {
            if ($item->getID() === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getById(string $id): EnumBasedDispatchableEntityInterface
    {
        $item = $this->findById($id);

        if (!$item) {
            throw new RepositoryException('Can not find item ":id" in repository :repo', [
                ':id'   => $id,
                ':repo' => static::getCodename(),
            ]);
        }

        return $item;
    }


    /**
     * @return EnumBasedDispatchableEntityInterface[]|array
     */
    public function getAll(): array
    {
        return $this->items;
    }

    /**
     * @param int $currentPage
     * @param int $itemsPerPage
     *
     * @return array|\BetaKiller\Model\EnumBasedDispatchableEntityInterface[]
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function getAllPaginated(int $currentPage, int $itemsPerPage): array
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @return \BetaKiller\Model\EnumBasedDispatchableEntityInterface[]
     */
    public function getAllAvailableItems(): array
    {
        return $this->getAll();
    }

    /**
     * Performs search for model item where the url key property is equal to $value
     *
     * @param string                                          $value
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \BetaKiller\Model\EnumBasedDispatchableEntityInterface|null
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function findItemByUrlKeyValue(
        string                $value,
        UrlContainerInterface $params
    ): ?EnumBasedDispatchableEntityInterface {
        throw new NotImplementedHttpException;
    }

    /**
     * Returns list of available items (model records) by url key property
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $parameters
     *
     * @return EnumBasedDispatchableEntityInterface[]
     */
    public function getItemsHavingUrlKey(UrlContainerInterface $parameters): array
    {
        // No filtering by parameters here
        return $this->getAll();
    }

    public function getUrlKeyName(): string
    {
        return UrlPrototype::KEY_ID;
    }

    /**
     * @return EnumBasedDispatchableEntityInterface[]
     */
    abstract protected function getItems(): array;
}
