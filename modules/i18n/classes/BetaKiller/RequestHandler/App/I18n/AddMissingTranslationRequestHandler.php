<?php
declare(strict_types=1);

namespace BetaKiller\RequestHandler\App\I18n;

use BetaKiller\Helper\ResponseHelper;
use BetaKiller\I18n\I18nFacade;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddMissingTranslationRequestHandler implements RequestHandlerInterface
{
    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $facade;

    /**
     * AddMissingTranslationRequestHandler constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade $facade
     */
    public function __construct(I18nFacade $facade)
    {
        $this->facade = $facade;
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $langIsoCode = $request->getAttribute('lang');

        $lang = $this->facade->getLanguageByIsoCode($langIsoCode);

//        $this->facade->registerMissingKey()

        // TODO: Implement handle() method.

        return ResponseHelper::successJson();
    }
}
