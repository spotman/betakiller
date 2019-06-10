<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Action\GetRequestActionInterface;
use BetaKiller\Action\PostRequestActionInterface;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Factory\ActionFactory;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\ActionModelInterface;
use BetaKiller\Url\UrlElementInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsFacade;
use Spotman\Defence\DefinitionBuilder;
use Spotman\Defence\DefinitionBuilderInterface;

/**
 * Action URL element processor
 */
class ActionUrlElementProcessor implements UrlElementProcessorInterface
{
    /**
     * @var \BetaKiller\Factory\ActionFactory
     */
    private $factory;

    /**
     * @var \Spotman\Defence\ArgumentsFacade
     */
    private $argumentsFacade;

    /**
     * @param \BetaKiller\Factory\ActionFactory $factory
     * @param \Spotman\Defence\ArgumentsFacade  $argumentsFacade
     */
    public function __construct(ActionFactory $factory, ArgumentsFacade $argumentsFacade)
    {
        $this->factory         = $factory;
        $this->argumentsFacade = $argumentsFacade;
    }

    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface      $model
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementProcessorException
     */
    public function process(UrlElementInterface $model, ServerRequestInterface $request): ResponseInterface
    {
        if (!$model instanceof ActionModelInterface) {
            throw new UrlElementProcessorException('Model must be instance of :must, but :real provided', [
                ':real' => get_class($model),
                ':must' => ActionModelInterface::class,
            ]);
        }

        $action = $this->factory->createFromUrlElement($model);

        try {
            if ($action instanceof GetRequestActionInterface) {
                // Fetch GET definition
                $getDefinition = $this->definitionBuilderFactory();
                $action->defineGetArguments($getDefinition);

                // Prepare arguments` data
                $getData      = $request->getQueryParams();
                $getArguments = $this->argumentsFacade->prepareArguments($getData, $getDefinition);

                $request = $request->withAttribute(ActionRequestHelper::GET_ATTRIBUTE, $getArguments);
            }

            if ($action instanceof PostRequestActionInterface) {
                // Fetch POST definition
                $postDefinition = $this->definitionBuilderFactory();
                $action->defineGetArguments($postDefinition);

                // Prepare arguments` data
                $postData      = ServerRequestHelper::getPost($request);
                $postArguments = $this->argumentsFacade->prepareArguments($postData, $postDefinition);

                $request = $request->withAttribute(ActionRequestHelper::POST_ATTRIBUTE, $postArguments);
            }
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException('Arguments validation error for action ":action": :error', [
                ':error'  => $e->getMessage(),
                ':action' => get_class($action),
            ]);
        }

        return $action->handle($request);
    }

    private function definitionBuilderFactory(): DefinitionBuilderInterface
    {
        return new DefinitionBuilder;
    }
}
