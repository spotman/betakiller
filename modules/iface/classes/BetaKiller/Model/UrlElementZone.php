<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Url\ZoneInterface;

class UrlElementZone extends \ORM implements ZoneInterface
{
    protected function configure(): void
    {
        $this->_table_name = 'url_element_zones';

        parent::configure();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->name;
    }
}
