<?php

namespace BetaKiller\Model;

use Exception;

class AclResourcePermission extends \ORM
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = 'acl_resource_permissions';

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
