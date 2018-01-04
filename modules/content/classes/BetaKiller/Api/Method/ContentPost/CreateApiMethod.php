<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityCreateApiMethod;
use BetaKiller\Status\StatusWorkflowFactory;
use Spotman\Api\ApiMethodException;

class CreateApiMethod extends AbstractEntityCreateApiMethod
{
    use ContentPostMethodTrait;

    /**
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    private $workflowFactory;

    public function __construct($data, StatusWorkflowFactory $factory)
    {
        parent::__construct($data);

        $this->workflowFactory = $factory;
    }

    /**
     * Implement this method
     *
     * @param \BetaKiller\Model\ContentPost                              $model
     * @param                                                            $data
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Status\StatusWorkflowException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \Kohana_Exception
     * @throws \Spotman\Api\ApiMethodException
     */
    protected function create($model, $data)
    {
        /** @var \BetaKiller\Status\ContentPostWorkflow $workflow */
        $workflow = $this->workflowFactory->create($model);

        $workflow->draft();

        if (isset($data->label)) {
            $model->setLabel($this->sanitizeString($data->label));
        }

        if (isset($data->type)) {
            $type = $this->sanitizeString($data->type);

            switch ($type) {
                case 'article':
                    $model->markAsArticle();
                    break;

                case 'page':
                    $model->markAsPage();
                    break;

                default:
                    throw new ApiMethodException('Unknown content post type :value', [':value' => $type]);
            }
        }

        // Return created model data
        return $model;
    }
}
