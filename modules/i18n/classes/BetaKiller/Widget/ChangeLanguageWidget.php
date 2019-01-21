<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\LanguageRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChangeLanguageWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $languageRepo;

    /**
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $languageRepo
     */
    public function __construct(LanguageRepositoryInterface $languageRepo)
    {
        $this->languageRepo = $languageRepo;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $currentLang = ServerRequestHelper::getI18n($request)->getLang();

        $stack = ServerRequestHelper::getUrlElementStack($request);

        // Link to current page in other language
        $element = $stack->hasCurrent()
            ? $stack->getCurrent()
            : $urlHelper->getDefaultUrlElement();

        // Add query parameters if exists
        $currentQuery = $request->getUri()->getQuery();

        $links = [];

        foreach ($this->languageRepo->getAppLanguages() as $lang) {
            $params = $urlHelper->createUrlContainer()->setEntity($lang);
            $url    = $urlHelper->makeUrl($element, $params, false);

            if ($currentQuery) {
                $url .= '?'.$currentQuery;
            }

            $data = [
                'url'   => $url,
                'code'  => $lang->getIsoCode(),
                'label' => $lang->getLabel(),
            ];

            $links[$lang->getIsoCode()] = $data;
        }

        return [
            'current'   => $currentLang->getIsoCode(),
            'lang_list' => $links,
        ];
    }
}
