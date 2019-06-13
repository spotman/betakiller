<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Action\ActionInterface;
use BetaKiller\Action\GetRequestActionInterface;
use BetaKiller\Action\PostRequestActionInterface;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\UrlElementInstanceInterface;
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
     * @var \Spotman\Defence\ArgumentsFacade
     */
    private $argumentsFacade;

    /**
     * @param \Spotman\Defence\ArgumentsFacade $argumentsFacade
     */
    public function __construct(ArgumentsFacade $argumentsFacade)
    {
        $this->argumentsFacade = $argumentsFacade;
    }

    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInstanceInterface $action
     * @param \Psr\Http\Message\ServerRequestInterface    $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\BadRequestHttpException
     */
    public function process(UrlElementInstanceInterface $action, ServerRequestInterface $request): ResponseInterface
    {
        if (!$action instanceof ActionInterface) {
            throw new UrlElementProcessorException('Instance must be :must, but :real provided', [
                ':real' => get_class($action),
                ':must' => ActionInterface::class,
            ]);
        }

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
                $action->definePostArguments($postDefinition);

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
