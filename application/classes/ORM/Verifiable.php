<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Model\UserInterface;

class ORM_Verifiable extends ORM
{
    protected function _initialize()
    {
        $this->belongs_to(array(

            'creator'   =>  array(
                'model' =>  'User',
                'foreign_key'   => 'created_by'
            ),

            'approver'  =>  array(
                'model' =>  'User',
                'foreign_key'   => 'approved_by'
            ),
        ));

        // Чтобы быстрее работала фильтрация неутверждённых записей
        $this->load_with(array('approver'));

        parent::_initialize();
    }

    /**
     * @return UserInterface
     */
    protected function get_approver_relation()
    {
        return $this->get('approver');
    }

    /**
     * @return UserInterface
     */
    public function get_approved_user()
    {
        return $this->get_approver_relation();
    }

    public function is_approved()
    {
        return $this->get_approver_relation()->loaded();
    }

    /**
     * @return UserInterface
     */
    protected function get_creator_relation()
    {
        return $this->get('creator');
    }

    /**
     * Устанавливает отметку об утверждении
     *
     * @param UserInterface $user
     * @return $this
     */
    public function approve(UserInterface $user)
    {
        if ( $this->is_approved() )
            return $this;

        return $this
            ->set('approver', $user)
            ->set('approved_at', DB::expr('CURRENT_TIMESTAMP'));
    }

    /**
     * Отмечает кем и когда была создана запись
     *
     * @param UserInterface $user
     * @return $this
     */
    public function set_creator(UserInterface $user)
    {
        return $this
            ->set('creator', $user)
            ->set('created_at', DB::expr('CURRENT_TIMESTAMP'));
    }

    /**
     * Returns TRUE if provided user created current record
     *
     * @param UserInterface $user
     * @return bool
     */
    public function is_creator(UserInterface $user)
    {
        return ( $user->pk() === $this->get_creator_relation()->pk() );
    }

    public function filter_approved_with_acl(UserInterface $current_user = NULL)
    {
        $is_moderator = $current_user && $current_user->isModerator();

        // Модератору показываем без фильтрации
        $all_allowed = $is_moderator;

        if ( ! $all_allowed )
        {
            // Фильтруем неподтверждённые варианты выбора
            $this->and_where_open()->filter_approved();

            // Если указан пользователь, показываем созданные им варианты
            if ( $current_user )
                $this->or_where("created_by", "=", $current_user->get_id());

            $this->and_where_close();
        }

        return $this;
    }

    public function filter_approved()
    {
        return $this->where($this->object_column("approved_by"), "IS NOT", NULL);
    }

    public function autocomplete($term, array $search_fields, $as_key_label_pairs = false)
    {
        $current_user = Env::user(TRUE);

        // Фильтруем неподтверждённые варианты выбора, если это нужно
        $this->filter_approved_with_acl($current_user);

        return parent::autocomplete($term, $search_fields, $as_key_label_pairs);
    }

    public function order_by_created_at($order = "DESC")
    {
        return $this->order_by('created_at', $order);
    }

    public function order_by_approved_at($order = "DESC")
    {
        return $this->order_by('approved_at', $order);
    }
}
