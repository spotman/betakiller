<?php

use BetaKiller\Status\AbstractStatusAclModelOrm;

class Model_ContentPostStatusAcl extends AbstractStatusAclModelOrm
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'content_post_status_acl';

        parent::_initialize();
    }
}
