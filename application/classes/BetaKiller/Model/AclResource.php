<?php
namespace BetaKiller\Model;

class AclResource extends AbstractOrmBasedSingleParentTreeModel
{
    public const URL_KEY = 'name';

    protected function _initialize()
    {
        $this->_table_name = 'acl_resources';

        parent::_initialize();
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
        $parent = $this->getParent();

        return $parent ? $parent->getCodename() : null;
    }
}
