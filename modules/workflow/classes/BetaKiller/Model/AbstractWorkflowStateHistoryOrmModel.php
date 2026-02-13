<?php

namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

abstract class AbstractWorkflowStateHistoryOrmModel extends AbstractCreatedByAt implements WorkflowStateHistoryInterface
{
    public const REL_ENTITY     = 'entity';
    public const COL_TRANSITION = 'transition';

    public static function createFrom(
        UserInterface $byUser,
        HasWorkflowStateWithHistoryInterface $entity,
        WorkflowStateInterface $state,
        string $transitionName
    ): static {
        $record = new static();

        $record
            ->bindToEntity($entity)
            ->bindToState($state)
            ->setTransitionName($transitionName)
            ->setCreatedAt()
            ->setCreatedBy($byUser);

        return $record;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->belongs_to([
            self::REL_ENTITY => [
                'model'       => static::getEntityModelName(),
                'foreign_key' => static::getEntityForeignKey(),
            ],
        ]);
    }

    abstract public static function getEntityModelName(): string;

    abstract public static function getEntityForeignKey(): string;

    abstract public function getState(): WorkflowStateInterface;

    abstract protected function bindToState(WorkflowStateInterface $state): static;

    public function getTransitionName(): string
    {
        return $this->get(self::COL_TRANSITION);
    }

    protected function bindToEntity(HasWorkflowStateWithHistoryInterface $entity): static
    {
        $this->setOnce(self::REL_ENTITY, $entity);

        return $this;
    }

    protected function setTransitionName(string $value): static
    {
        $this->setOnce(self::COL_TRANSITION, $value);

        return $this;
    }

}
