<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UrlHelperMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Factory\UrlHelperFactory
     */
    private $factory;

    /**
     * UrlHelperMiddleware constructor.
     *
     * @param \BetaKiller\Factory\UrlHelperFactory $factory
     */
    public function __construct(UrlHelperFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pack = RequestProfiler::begin($request, 'UrlHelper middleware');

        $params = ResolvingUrlContainer::create();
        $stack  = new UrlElementStack($params);

        $params->setQueryParts($request->getQueryParams());

        $urlHelper = $this->factory->create($params, $stack);

        $request = $request
            ->withAttribute(UrlElementStack::class, $stack)
            ->withAttribute(UrlContainerInterface::class, $params)
            ->withAttribute(UrlHelperInterface::class, $urlHelper);

        RequestProfiler::end($pack);

        return $handler->handle($request);
    }
}
