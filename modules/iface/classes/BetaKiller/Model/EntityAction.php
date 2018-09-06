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
    protected function configure(): void
    {
        $this->_table_name = 'entity_actions';

        parent::configure();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->get('name');
    }
}
