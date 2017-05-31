<?php
namespace BetaKiller\Api\Method;

use Spotman\Api\Method\AbstractApiMethod;

abstract class AbstractEntityBasedApiMethod extends AbstractApiMethod implements EntityBasedApiMethodInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \BetaKiller\Model\AbstractEntityInterface
     */
    private $entity;

    /**
     * Returns new model or performs search by id
     *
     * @param int|null $id
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     */
    abstract protected function createEntity($id = NULL);

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface
     */
    public function getEntity()
    {
        if (!$this->entity) {
            $this->entity = $this->createEntity($this->id);
        }

        return $this->entity;
    }
}
