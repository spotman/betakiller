<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\AbstractEntityInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;

abstract class AbstractEntityUpdateApiApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @var \stdClass
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;

        if (empty($this->data->id)) {
            throw new ApiMethodException('Can not update entity with empty id');
        }

        $this->id = (int)$this->data->id;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(): ?ApiMethodResponse
    {
        $model = $this->getEntity();
        $response = $this->update($model, $this->data);

        return $this->response($response);
    }

    /**
     * Override this method
     *
     * @param $model
     * @param $data
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|null
     */
    abstract protected function update($model, $data): AbstractEntityInterface;
}
