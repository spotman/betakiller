<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class MissingUrlRedirectTarget extends \ORM implements MissingUrlRedirectTargetModelInterface
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
        $this->_table_name = 'redirect_targets';
    }

    public function getUrl(): string
    {
        return $this->get('url');
    }

    public function setUrl(string $value): MissingUrlRedirectTargetModelInterface
    {
        return $this->set('url', $value);
    }
}
