<?php defined('SYSPATH') OR die('No direct script access.');

class Model_AclRule extends ORM
{
    protected $_table_name = 'acl_rules';

    protected $_reload_on_wakeup = FALSE;
}
