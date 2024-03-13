<?php

namespace BetaKiller\Model;

class ContentPostState extends AbstractWorkflowStateOrm
{
    public const DRAFT         = 'draft';
    public const PENDING       = 'pending';
    public const PUBLISHED     = 'published';
    public const PAUSED        = 'paused';
    public const FIX_REQUESTED = 'fix-requested';

    protected function configure(): void
    {
        $this->_table_name = 'content_post_statuses';
    }

    /**
     * Returns name of I18n key to proceed
     *
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return 'content-post-state.'.$this->getCodename();
    }
}
