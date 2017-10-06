<?php
namespace BetaKiller\Model;

class RefInternalPage extends \ORM
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize(): void
    {
        $this->_table_name = 'ref_internal_links';

        parent::_initialize();
    }

    public function setUri(string $uri): RefInternalPage
    {
        $this->set('uri', $uri);
        return $this;
    }

    public function incrementHits(): void
    {
        $this->setHits($this->getHits() + 1);
    }

    public function setHits(int $value): RefInternalPage
    {
        $this->set('hits', $value);
        return $this;
    }

    public function getUri(): string
    {
        return $this->get('uri');
    }

    public function getHits(): int
    {
        return (int)$this->get('hits');
    }
}
