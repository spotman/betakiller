<?php defined('SYSPATH') OR die('No direct script access.');

class ACL extends ACL_Core
{
    /**
     * @todo нормальный механизм ролей
     * @static
     * @param string $resource
     * @param string $action
     * @param string|NULL $role
     * @return mixed
     */
    public static function allowed($resource, $action, $role = NULL)
    {
        $acl = Env::acl();

        if ( $role === NULL )
        {
            $role = Env::role();
        }

        return $acl->is_allowed($role, $resource, $action);
    }

    public static function factory($module)
    {
        $acl = new ACL;
        $acl->load($module);
        return $acl;
    }


    /**
     * Подгрузка ACL
     *
     * @todo переписать нормально на новую модель разрешений
     * @param $resource
     * @return $this|null
     */
    protected function load($resource)
    {
        // ACL кэшируются в сессию
        if ( ! is_null(Session::instance()->get('acl')) )
        {
            $profiler_acl_from_cache = Profiler::start('ACL', 'acl_from_cache');
            $acl = Session::instance()->get('acl');
            if ( isset($acl[$resource]) )
            {
                Profiler::stop($profiler_acl_from_cache);
                return $acl[$resource];
            }
            Profiler::stop($profiler_acl_from_cache);
        }

        $profiler_load = Profiler::start('ACL', 'load');

        $this->deny(NULL, NULL, NULL);

        // Ищем текущий ресурс
        $global_resource = ORM::factory('Resource')->where('alias', '=', $resource)
            ->cached(Kohana::$config->load('clt.aclrule'))
            ->find();

        // Если ресурса нет в acl_resource_2, то выходим
        if ( ! $global_resource->loaded() )
            return NULL;

        // Ищем текущую локальную роль
        $role = ORM::factory('Localrole')->where('alias', '=', Env::get('role'))
            ->cached(Kohana::$config->load('clt.localrole'))
            ->find();

        // Если роль не найдена, то выходим
        if ( ! $role->loaded() )
            return NULL;

        // Добавляем локальную роль. Локальная роль реализует интерфейс ACL_Role_Interface.
        $this->add_role($role);

        // Ищем корневой элемент в дереве ( у корневого элемента left = 1 )

        /** @var ORM_MPTT $root_mptt */
        $root_mptt = ORM_MPTT::factory('AclResource');

        /** @var ORM_MPTT $root */
        $root = $root_mptt->root($global_resource->pk());

        // Составляем полный массив ресурсов, включая корневой элемент.  Вид массива: [id => Model_AclResource]
        $tree = $root->descendants()->as_array('id') + array($root->pk() => $root);

        // Если массив ресурсов пустой, то сохраняем пустой объект ACL в сессию
        if ( empty($tree) )
        {
            $this->save_to_session($resource);
            return $this;
        }

        // Добавляем дерево ресурсов в ACL
        foreach ( $tree as $child_resource )
        {
            $parent_name = NULL;
            if ( in_array($child_resource->parent_id, array_keys($tree)) )
            {
                $parent_name = $tree[$child_resource->parent_id]->name;
            }
            $this->add_resource($child_resource->name, $parent_name);
        }

        // Ищем правила для роли и ресурсов
        $aclrules = ORM::factory('AclRule')->and_where('resource_id', 'IN', array_keys($tree))
            ->and_where('role', '=', Env::get('role'))
            ->cached(Kohana::$config->load('clt.aclrule'))
            ->find_all();

        foreach ( $aclrules as $rule )
        {
            /** @var Model_AclRule $rule */
            if ( $rule->type == 'allow' )
            {
                $assert = NULL;
                if ( ! is_null($rule->assert) )
                {
                    $classname = 'Acl_Assert_'.Text::ucfirst($rule->assert, '_');
                    if ( class_exists($classname) )
                    {
                        $assert = new $classname;
                    }
                }

                $this->allow($rule->role, $tree[$rule->resource_id]->name, $rule->action, $assert);
            }
        }

        // Сохраняем ACL в сессию
        $this->save_to_session($resource);

        Profiler::stop($profiler_load);

        return $this;
    }

    /**
     * Сохранение текущего состояния ACL в сессию
     * @param string $resource
     */
    private function save_to_session($resource)
    {
        if ( ! is_null(Session::instance()->get('acl')) )
        {
            $profiler_union = Profiler::start('ACL', $resource.'_acl_union');
            Session::instance()->set('acl', array_merge(array($resource => $this), Session::instance()->get('acl')));
            Profiler::stop($profiler_union);
        }
        else
        {
            $profiler_define = Profiler::start('ACL', $resource.'_acl_define');
            Session::instance()->set('acl', array($resource => $this));
            Profiler::stop($profiler_define);
        }
    }

}
