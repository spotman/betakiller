<?php
namespace BetaKiller\Model;

use ORM;

class IFaceZone extends ORM
{
    const PUBLIC_ZONE   = 'public';
    const ADMIN_ZONE    = 'admin';
    const PERSONAL_ZONE = 'personal';
    const PREVIEW_ZONE  = 'preview';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize(): void
    {
        $this->_table_name = 'iface_zones';

        parent::_initialize();
    }

    /**
     * @return string|null
     * @throws \Kohana_Exception
     */
    public function getName(): ?string
    {
        return (string)$this->get('name');
    }
}
