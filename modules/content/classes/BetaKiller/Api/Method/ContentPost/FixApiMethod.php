<?php
namespace BetaKiller\Api\Method\ContentPost;

use Spotman\Api\Method\AbstractModelBasedApiMethod;

class FixApiMethod extends AbstractModelBasedApiMethod
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
    public function execute()
    {
        /** @var \Model_ContentPost $model */
        $model = $this->getModel();

        $model->fix()->save();

        return null;
    }
}
