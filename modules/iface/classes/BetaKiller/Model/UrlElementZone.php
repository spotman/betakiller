<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Url\ZoneInterface;

class UrlElementZone extends \ORM implements ZoneInterface
{
    public const TABLE_FIELD_NAME = 'name';

    protected function configure(): void
    {
        $this->_table_name = 'url_element_zones';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->get(self::TABLE_FIELD_NAME);
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->set(self::TABLE_FIELD_NAME, $name);
    }
}
