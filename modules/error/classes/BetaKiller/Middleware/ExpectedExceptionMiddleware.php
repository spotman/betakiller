<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Exception\PublicException;
use BetaKiller\Exception\RedirectException;
use BetaKiller\Exception\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

class ExpectedExceptionMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (RedirectException $e) {
            $httpCode = $e->getCode();
            $url      = $e->getLocation();

            return new RedirectResponse($url, $httpCode);
        } catch (ValidationException $e) {
            $message = $e->getFirstItem()->getMessage();

            throw new PublicException($message);
        } catch (\Throwable $e) {
            // Rethrow other exceptions
            throw $e;
        }
    }
}
