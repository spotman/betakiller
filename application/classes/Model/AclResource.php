<?php defined('SYSPATH') OR die('No direct script access.');

class Model_AclResource extends ORM_MPTT
{
    protected $_table_name = 'acl_resources';

    protected $_reload_on_wakeup = FALSE;
}
