<?php
namespace BetaKiller\Model;

class RefPage extends \ORM
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
        $this->_table_name = 'ref_pages';

        $this->belongs_to([
            'domain' => [
                'model'       => 'RefDomain',
                'foreign_key' => 'domain_id',
            ],
        ]);

        $this->load_with(['domain']);
    }

    public function setDomain(RefDomain $domain): RefPage
    {
        $this->set('domain', $domain);

        return $this;
    }

    public function setUri(string $uri): RefPage
    {
        $this->set('uri', $uri);

        return $this;
    }

    public function incrementHits(): void
    {
        $this->setHits($this->getHits() + 1);
    }

    public function setHits(int $value): RefPage
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

    public function isIgnored(): bool
    {
        return $this->getDomain()->isIgnored();
    }

    /**
     * @return \BetaKiller\Model\RefDomain
     */
    private function getDomain(): RefDomain
    {
        return $this->get('domain');
    }
}
