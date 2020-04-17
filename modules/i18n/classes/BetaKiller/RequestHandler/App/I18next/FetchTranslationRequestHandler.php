<?php
declare(strict_types=1);

namespace BetaKiller\RequestHandler\App\I18next;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\TextHelper;
use BetaKiller\I18n\I18nFacade;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FetchTranslationRequestHandler implements RequestHandlerInterface
{
    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $facade;

    /**
     * FetchTranslationRequestHandler constructor.
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
        $p = RequestProfiler::begin($request, 'Translation table');

        $domain = $request->getHeader('X-I18n-Domain')[0] ?? null;

        $langIsoCode = $request->getAttribute('lang');

        $lang = $this->facade->getLanguageByIsoCode($langIsoCode);

        $keys = [
            'frontend.',
        ];

        if ($domain === 'admin') {
            $keys[] = 'admin.frontend.';
        }

        $data = [];

        foreach ($this->facade->getAllTranslationKeys() as $item) {
            $name = $item->getI18nKeyName();

            foreach ($keys as $key) {
                if (TextHelper::startsWith($name, $key)) {
                    $data[$name] = $this->facade->translateKey($lang, $item);
                    break;
                }
            }
        }

        RequestProfiler::end($p);

        return ResponseHelper::json($data);
    }
}
