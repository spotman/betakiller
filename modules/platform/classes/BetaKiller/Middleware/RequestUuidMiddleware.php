<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use PhpMiddleware\RequestId\Generator\RamseyUuid4Generator;
use PhpMiddleware\RequestId\RequestIdMiddleware;
use PhpMiddleware\RequestId\RequestIdProviderFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\UuidFactoryInterface;

final class RequestUuidMiddleware implements MiddlewareInterface
{
    /**
     * @var \Ramsey\Uuid\UuidFactoryInterface
     */
    private $uuidFactory;

    /**
     * RequestUuidMiddleware constructor.
     *
     * @param \Ramsey\Uuid\UuidFactoryInterface $uuidFactory
     */
    public function __construct(UuidFactoryInterface $uuidFactory)
    {
        $this->uuidFactory = $uuidFactory;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $generator         = new RamseyUuid4Generator($this->uuidFactory);
        $requestIdProvider = new RequestIdProviderFactory($generator);

        $proxy = new RequestIdMiddleware($requestIdProvider);

        return $proxy->process($request, $handler);
    }
}
