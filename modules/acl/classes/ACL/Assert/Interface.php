<?php defined('SYSPATH') OR die('No direct script access.');

interface ACL_Assert_Interface {

	public function assert(ACL $acl, $role = NULL, $resource = NULL, $privilege = NULL);

}
