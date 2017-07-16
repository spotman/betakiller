<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use Spotman\Api\ApiMethodResponse;

class ApproveApiMethod extends AbstractEntityBasedApiMethod
{
    use ContentCommentMethodTrait;

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
        /** @var \BetaKiller\Model\ContentComment $model */
        $model = $this->getEntity();

        $model->approve()->save();

        return null;
    }
}
