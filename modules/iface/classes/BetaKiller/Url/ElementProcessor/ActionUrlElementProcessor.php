<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Factory\ActionFactory;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\ActionModelInterface;
use BetaKiller\Url\UrlElementInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsFacade;

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
                ':real' => \get_class($model),
                ':must' => ActionModelInterface::class,
            ]);
        }

        $action = $this->factory->createFromUrlElement($model);

        try {
            $postData   = ServerRequestHelper::getPost($request);
            $definition = $action->getArgumentsDefinition();
            $arguments  = $this->argumentsFacade->prepareArguments($postData, $definition);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException('Arguments validation error for action ":action": :error', [
                ':error'  => $e->getMessage(),
                ':action' => get_class($action),
            ]);
        }

        return $action->handle($request, $arguments);
    }
}
