<?php
declare(strict_types=1);

namespace BetaKiller\RequestHandler\App\I18next;

use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
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

        $added = [];

        foreach (ServerRequestHelper::getPost($request) as $key => $value) {
            // PHP converts dots to underscores in POST key names
            $key = \str_replace('_', '.', $key);

            $this->facade->registerMissingKey($key, $lang);
            $added[] = $key;
        }

        return ResponseHelper::successJson($added);
    }
}
