<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Factory\ActionFactory;
use BetaKiller\Url\ActionModelInterface;
use BetaKiller\Url\UrlElementInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action URL element processor
 */
class ActionUrlElementProcessor implements UrlElementProcessorInterface
{
    /**
     * @var \BetaKiller\Factory\ActionFactory
     */
    private $factory;

    public function __construct(ActionFactory $factory)
    {
        $this->factory = $factory;
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

        return $action->handle($request);
    }
}
