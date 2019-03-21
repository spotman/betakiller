<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Model\AbstractEntityInterface;

class EntityFactory implements EntityFactoryInterface
{
    /**
     * @var \BetaKiller\Factory\OrmFactory
     */
    private $ormFactory;

    /**
     * EntityFactory constructor.
     *
     * @param \BetaKiller\Factory\OrmFactory $ormFactory
     */
    public function __construct(OrmFactory $ormFactory)
    {
        $this->ormFactory = $ormFactory;
    }

    public function create(string $name): AbstractEntityInterface
    {
        // Using ORM as a default entity source
        $entity = $this->ormFactory->create($name);

        if (!$entity instanceof AbstractEntityInterface) {
            throw new FactoryException('Entity ":name" must implement :interface', [
                ':name'      => $name,
                ':interface' => AbstractEntityInterface::class,
            ]);
        }

        return $entity;
    }
}
