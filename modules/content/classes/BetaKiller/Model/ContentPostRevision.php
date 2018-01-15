<?php
namespace BetaKiller\Model;

class ContentPostRevision extends AbstractRevisionOrmModel
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
        $this->_table_name = 'content_post_revisions';

        parent::_initialize();
    }

    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules()
    {
        return parent::rules() + [
                'label' => [
                    ['not_empty'],
                ],
            ];
    }
}
