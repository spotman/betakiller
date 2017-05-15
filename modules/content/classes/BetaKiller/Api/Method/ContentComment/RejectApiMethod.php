<?php
namespace BetaKiller\Api\Method\ContentComment;

use Spotman\Api\Method\AbstractModelBasedApiMethod;

class RejectApiMethod extends AbstractModelBasedApiMethod
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
    public function execute()
    {
        /** @var \Model_ContentComment $model */
        $model = $this->getModel();

        $model->reject()->save();

        return null;
    }
}
