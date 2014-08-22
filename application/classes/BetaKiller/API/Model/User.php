<?php defined('SYSPATH') OR die('No direct script access.');


abstract class BetaKiller_API_Model_User extends API_Model {

    public function update_profile($data)
    {
        $user = $this->current_user();

        $data = (object) $data;

        if ( isset($data->firstName) )
            $user->set_first_name(HTML::chars($data->firstName));

        if ( isset($data->lastName) )
            $user->set_last_name(HTML::chars($data->lastName));

        if ( isset($data->phone) )
            $user->set_phone(HTML::chars($data->phone));

        $user->update();
    }

    /**
     * Returns new model or performs search by id
     *
     * @param null $id
     * @return Model
     */
    protected function model($id = NULL)
    {
        return $this->orm_model_factory('User', $id);
    }

}
