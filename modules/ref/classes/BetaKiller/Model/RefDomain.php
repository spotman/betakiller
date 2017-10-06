<?php
namespace BetaKiller\Model;

class RefDomain extends \ORM
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
        $this->_table_name = 'ref_domains';

        parent::_initialize();
    }

    public function setName(string $name): RefDomain
    {
        $this->set('name', $name);
        return $this;
    }

    public function getName(): string
    {
        return $this->get('name');
    }

    public function markAsInternal(): void
    {
        $this->set('is_internal', 1);
    }

    public function markAsExternal(): void
    {
        $this->set('is_internal', 0);
    }

    public function markAsIgnored(): void
    {
        $this->set('is_ignored', 1);
    }

    public function markAsActive(): void
    {
        $this->set('is_ignored', 0);
    }

    public function isIgnored(): bool
    {
        return (bool)$this->get('is_ignored');
    }
}
