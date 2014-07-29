<?php defined('SYSPATH') OR die('No direct script access.');

class ORM_Verifiable extends ORM {

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
     * @return Model_User
     */
    protected function get_approver_relation()
    {
        return $this->get('approver');
    }

    /**
     * @return Model_User
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
     * @return Model_User
     */
    protected function get_creator_relation()
    {
        return $this->get('creator');
    }

    /**
     * Устанавливает отметку об утверждении
     *
     * @param Model_User $user
     * @return $this
     */
    public function approve(Model_User $user)
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
     * @param Model_User $user
     * @return $this
     */
    public function set_creator(Model_User $user)
    {
        return $this
            ->set('creator', $user)
            ->set('created_at', DB::expr('CURRENT_TIMESTAMP'));
    }

    public function filter_approved(Model_User $current_user)
    {
        $is_moderator = $current_user->is_moderator();

        // Модератору показываем без фильтрации
        $all_allowed = $is_moderator;

        if ( ! $all_allowed )
        {
            // Фильтруем неподтверждённые варианты выбора
            $this
                ->and_where_open()
                ->where("approved_by", "IS NOT", NULL)
                ->or_where("created_by", "=", $current_user->get_id())
                ->and_where_close();
        }

        return $this;
    }

    protected function _autocomplete($query, array $search_fields)
    {
        $current_user = Env::user();

        // Фильтруем неподтверждённые варианты выбора, если это нужно
        $this->filter_approved($current_user);

        return parent::_autocomplete($query, $search_fields);
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