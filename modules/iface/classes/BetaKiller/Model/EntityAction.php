<?php
namespace BetaKiller\Model;

use ORM;

class EntityAction extends ORM
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \BetaKiller\Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'entity_actions';

        parent::_initialize();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->get('name');
    }
}
