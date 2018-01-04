<?php
namespace BetaKiller\Api\Method;

use Spotman\Api\ApiMethodResponse;

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
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function execute(): ?ApiMethodResponse
    {
        $entity       = $this->getEntity();
        $responseData = $this->create($entity, $this->data);

        $this->saveEntity();

        return $this->response($responseData);
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface      $model
     * @param                                                $data
     *
     * @return mixed
     */
    abstract protected function create($model, $data);
}
