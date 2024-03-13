<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityCreateApiMethod;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\UserInterface;
use BetaKiller\Workflow\ContentPostWorkflow;
use Spotman\Api\ApiMethodException;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

final class CreateApiMethod extends AbstractEntityCreateApiMethod
{
    private const ARG_DATA = 'data';

    private const ARG_LABEL = 'label';
    private const ARG_TYPE  = 'type';

    public function __construct(private readonly ContentPostWorkflow $workflow)
    {
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->compositeStart(self::ARG_DATA)
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
     * @throws \BetaKiller\Workflow\WorkflowStateException
     * @throws \Spotman\Api\ApiMethodException
     */
    protected function create(ArgumentsInterface $arguments, UserInterface $user)
    {
        $model = new ContentPost();

        $this->workflow->draft($model);

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

        // Return created model
        return $model;
    }
}
