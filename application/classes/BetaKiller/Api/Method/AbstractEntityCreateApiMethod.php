<?php
namespace BetaKiller\Api\Method;

abstract class AbstractEntityCreateApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @var \stdClass
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        $entity = $this->getEntity();
        $responseData = $this->create($entity, $this->data);

        return $this->response($responseData);
    }

    /**
     * Override this method
     *
     * @param $model
     * @param $data
     *
     * @throws \Spotman\Api\ApiMethodException
     * @return \BetaKiller\Model\AbstractEntityInterface
     */
    abstract protected function create($model, $data);
}
