<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\I18n\I18nFacade;
use Middlewares\ContentLanguage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ContentNegotiationMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * I18nMiddleware constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade $i18n
     */
    public function __construct(I18nFacade $i18n)
    {
        $this->i18n = $i18n;
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
        $langMiddleware = new ContentLanguage($this->i18n->getAllowedLanguagesNames());

        return $langMiddleware->process($request, $handler);
    }
}
