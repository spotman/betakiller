<?php
namespace BetaKiller\Model;

use ORM;

class UrlElementZone extends ORM
{
    public const PUBLIC_ZONE   = 'public';
    public const ADMIN_ZONE    = 'admin';
    public const PERSONAL_ZONE = 'personal';
    public const PREVIEW_ZONE  = 'preview';

    protected function configure(): void
    {
        $this->_table_name = 'url_element_zones';

        parent::configure();
    }

    /**
     * @return string|null
     * @throws \Kohana_Exception
     */
    public function getName(): string
    {
        return (string)$this->get('name');
    }
}
