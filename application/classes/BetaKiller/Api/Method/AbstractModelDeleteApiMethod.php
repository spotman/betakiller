<?php
namespace BetaKiller\Api\Method;

use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;

abstract class AbstractModelDeleteApiMethod extends AbstractEntityBasedApiMethod
{
    public function __construct($id)
    {
        $this->id = (int)$id;

        if (!$this->id) {
            throw new ApiMethodException('Can not delete entity with empty id');
        }
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(): ?ApiMethodResponse
    {
        $model = $this->getEntity();

        return $this->response($this->delete($model));
    }

    /**
     * Implement this method
     *
     * @param $model
     *
     * @throws \Spotman\Api\ApiMethodException
     * @return bool
     */
    abstract protected function delete($model);
}
