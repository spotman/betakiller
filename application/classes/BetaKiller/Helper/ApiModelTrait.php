<?php
namespace BetaKiller\Helper;

use ORM;
use Spotman\Api\ApiMethodException;

trait ApiModelTrait
{
    use CurrentUserTrait;

    /**
     * @param string   $name
     * @param int|NULL $id
     *
     * @return \BetaKiller\Utils\Kohana\ORM\OrmInterface|\Spotman\Api\AbstractCrudMethodsModelInterface
     * @throws ApiMethodException
     */
    protected function orm_model_factory($name, $id = NULL)
    {
        $model = ORM::factory($name, $id);

        if ($id && !$model->loaded())
            throw new ApiMethodException('Incorrect id :id for model :model',
                [':id' => $id, ':model' => $name]);

        return $model;
    }

    protected function trim(& $value)
    {
        $value = trim($value);

        return $value;
    }
}
