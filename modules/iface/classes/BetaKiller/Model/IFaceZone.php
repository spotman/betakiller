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
     * @throws \BetaKiller\Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'iface_zones';

        parent::_initialize();
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->get('name');
    }
}
