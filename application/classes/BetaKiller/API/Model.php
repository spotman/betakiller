<?php

abstract class BetaKiller_API_Model extends Core_API_Model
{
    use BetaKiller\Helper\CurrentUser;

    /**
     * @param string $name
     * @param int|NULL $id
     * @return ORM
     * @throws API_Model_Exception
     */
    protected function orm_model_factory($name, $id = NULL)
    {
        $model = ORM::factory($name, $id);

        if ( $id && !$model->loaded() )
            throw new API_Model_Exception('Incorrect id :id for model :model',
                array(':id' => $id, ':model' => $name));

        return $model;
    }

    protected function trim(& $value)
    {
        $value = trim($value);
        return $value;
    }
}
