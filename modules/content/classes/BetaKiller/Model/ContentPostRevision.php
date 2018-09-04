<?php
namespace BetaKiller\Model;

class ContentPostRevision extends AbstractRevisionOrmModel
{
    public const URL_KEY_NAME = 'id';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = 'content_post_revisions';

        parent::configure();
    }

    protected function getRelatedEntityModelName(): string
    {
        return 'ContentPost';
    }

    protected function getRelatedEntityForeignKey(): string
    {
        return 'post_id';
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
