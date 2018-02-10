<?php
namespace BetaKiller\Model;

abstract class AbstractOrmBasedSingleParentTreeModel extends \ORM implements SingleParentTreeModelInterface
{
    protected function _initialize()
    {
        $this->belongs_to([
            'parent' => [
                'model'       => $this->getModelName(),
                'foreign_key' => $this->getParentIdColumnName(),
            ],
        ]);

        $this->load_with(['parent']);

        parent::_initialize();
    }

    protected function getParentIdColumnName(): string
    {
        return 'parent_id';
    }

    /**
     * Return parent iface model or NULL
     *
     * @return \BetaKiller\Model\SingleParentTreeModelInterface|static|null
     */
    public function getParent()
    {
        /** @var static $parent */
        $parent = $this->get('parent');

        return $parent->loaded() ? $parent : null;
    }

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface $parent
     *
     * @throws \Kohana_Exception
     */
    public function setParent(SingleParentTreeModelInterface $parent = null)
    {
        $this->set('parent', $parent);
    }

    /**
     * @return SingleParentTreeModelInterface[]
     */
    public function getAllParents(): array
    {
        $current = $this;
        $parents = [];

        do {
            $current = $current->getParent();

            if ($current) {
                $parents[] = $current;
            }
        } while($current);

        return $parents;
    }
}
