<?php
namespace BetaKiller\Model;

class AclResource extends AbstractOrmBasedSingleParentTreeModel
{
    public const URL_KEY = 'name';

    protected function configure(): void
    {
        $this->_table_name = 'acl_resources';

        parent::configure();
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get('codename');
    }

    /**
     * @return null|string
     */
    public function getParentResourceCodename(): ?string
    {
        /** @var \BetaKiller\Model\AclResource|null $parent */
        $parent = $this->getParent();

        return $parent ? $parent->getCodename() : null;
    }
}
