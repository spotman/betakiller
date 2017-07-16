<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use Spotman\Api\ApiMethodResponse;

class FixApiMethod extends AbstractEntityBasedApiMethod
{
    use ContentPostMethodTrait;

    /**
     * ApproveApiMethod constructor.
     *
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = (int)$id;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentPost $model */
        $model = $this->getEntity();

        $model->fix()->save();

        return null;
    }
}
