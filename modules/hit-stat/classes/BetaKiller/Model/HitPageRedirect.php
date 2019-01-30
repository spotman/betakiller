<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class HitPageRedirect extends \ORM implements HitPageRedirectInterface
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
        $this->_table_name = 'stat_hit_page_redirects';
    }

    public function getUrl(): string
    {
        return $this->get('url');
    }

    public function setUrl(string $value): HitPageRedirectInterface
    {
        return $this->set('url', $value);
    }
}
