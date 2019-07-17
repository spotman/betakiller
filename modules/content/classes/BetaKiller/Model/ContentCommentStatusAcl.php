<?php
namespace BetaKiller\Model;

use BetaKiller\Workflow\AbstractStatusAclModelOrm;

class ContentCommentStatusAcl extends AbstractStatusAclModelOrm
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = 'content_comment_status_acl';

        parent::configure();
    }
}
