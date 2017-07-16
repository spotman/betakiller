<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use Spotman\Api\ApiMethodResponse;

class RejectApiMethod extends AbstractEntityBasedApiMethod
{
    use ContentCommentMethodTrait;

    /**
     * RejectApiMethod constructor.
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

        $model->reject()->save();

        return null;
    }
}
