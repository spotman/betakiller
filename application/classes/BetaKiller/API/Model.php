<?php defined('SYSPATH') OR die('No direct script access.');


abstract class BetaKiller_API_Model extends Core_API_Model {

    /**
     * @param string $name
     * @param int|NULL $id
     * @return ORM
     * @throws API_Model_Exception
     */
    protected function orm_model_factory($name, $id = NULL)
    {
        $model = ORM::factory($name, $id);

        if ( $id AND ! $model->loaded() )
            throw new API_Model_Exception('Incorrect id :id for model :model',
                array(':id' => $id, ':model' => $name));

        return $model;
    }

    protected function current_user($allow_guest = FALSE)
    {
        return Env::user($allow_guest);
    }

    protected function trim(& $value)
    {
        $value = trim($value);
        return $value;
    }

//    protected function search_by_uri($uri)
//    {
//        $model_result = $this->model()->filter_uri($uri)->find();
//
//        return $this->response( $model_result->loaded() ? $model_result : NULL );
//    }

}
