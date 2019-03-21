<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityCreateApiMethod;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\UserInterface;
use BetaKiller\Status\StatusWorkflowFactory;
use Spotman\Api\ApiMethodException;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class CreateApiMethod extends AbstractEntityCreateApiMethod
{
    private const ARG_DATA = 'data';

    private const ARG_LABEL = 'label';
    private const ARG_TYPE  = 'type';

    /**
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    private $workflowFactory;

    public function __construct(StatusWorkflowFactory $factory)
    {
        $this->workflowFactory = $factory;
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->composite(self::ARG_DATA)
            ->string(self::ARG_LABEL)->optional()
            ->string(self::ARG_TYPE)->optional()->whitelist(['article', 'page']);
    }

    /**
     * Implement this method
     *
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \BetaKiller\Model\ContentPostInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Status\StatusWorkflowException
     * @throws \Spotman\Api\ApiMethodException
     */
    protected function create(ArgumentsInterface $arguments, UserInterface $user)
    {
        $model = new ContentPost();

        /** @var \BetaKiller\Status\ContentPostWorkflow $workflow */
        $workflow = $this->workflowFactory->create($model);

        $workflow->draft();

        $data = $arguments->getArray(self::ARG_DATA);

        if (isset($data[self::ARG_LABEL])) {
            $model->setLabel($data[self::ARG_LABEL]);
        }

        if (isset($data[self::ARG_TYPE])) {
            $type = $data[self::ARG_TYPE];

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
