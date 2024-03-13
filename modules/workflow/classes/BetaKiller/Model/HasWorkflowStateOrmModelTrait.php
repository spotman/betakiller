<?php
namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

trait HasWorkflowStateOrmModelTrait
{
    use HasWorkflowStateBaseTrait;

    protected function configureWorkflowStateRelation(): void
    {
        $statusRelationKey = static::getWorkflowStateRelationKey();

        $this->belongs_to([
            $statusRelationKey => [
                'model'       => static::getWorkflowStateModelName(),
                'foreign_key' => static::getWorkflowStateForeignKey(),
            ],
        ]);

        $this->load_with([$statusRelationKey]);
    }

    /**
     * @return WorkflowStateInterface
     */
    public function getWorkflowState(): WorkflowStateInterface
    {
        return $this->getRelatedEntity(static::getWorkflowStateRelationKey());
    }

    /**
     * @return bool
     */
    public function hasWorkflowState(): bool
    {
        return (bool)$this->get(static::getWorkflowStateForeignKey());
    }

    /**
     * @param WorkflowStateInterface $target
     *
     * @return void
     * @throws \Kohana_Exception
     */
    protected function setWorkflowState(WorkflowStateInterface $target): void
    {
        $this->set(static::getWorkflowStateRelationKey(), $target);
    }

    public static function getWorkflowStateRelationKey(): string
    {
        return static::getModelName().'_status';
    }

    /**
     * @return string
     */
    abstract public static function getWorkflowStateForeignKey(): string;
}
