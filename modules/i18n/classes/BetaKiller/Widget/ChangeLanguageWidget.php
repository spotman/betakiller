<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\LanguageRepository;
use Psr\Http\Message\ServerRequestInterface;

class ChangeLanguageWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Repository\LanguageRepository
     */
    private $languageRepo;

    /**
     * @param \BetaKiller\Repository\LanguageRepository $languageRepo
     */
    public function __construct(LanguageRepository $languageRepo)
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

        $currentLangName = ServerRequestHelper::getI18n($request)->getLang();

        $stack = ServerRequestHelper::getUrlElementStack($request);

        // Link to current page in other language
        $element = $stack->hasCurrent()
            ? $stack->getCurrent()
            : $urlHelper->getUrlElementByCodename('App_Index');

        // Add query parameters if exists
        $currentQuery = $request->getUri()->getQuery();

        $links = [];

        foreach ($this->languageRepo->getAllSystem() as $lang) {
            $params = $urlHelper->createUrlContainer()->setEntity($lang);
            $url    = $urlHelper->makeUrl($element, $params, false);

            if ($currentQuery) {
                $url .= '?'.$currentQuery;
            }

            $data = [
                'url'   => $url,
                'label' => $lang->getLabel(),
            ];

            if ($lang->getIsoCode() === $currentLangName) {
                \array_unshift($links, $data);
            } else {
                $links[] = $data;
            }
        }

        return [
            'lang_list' => $links,
        ];
    }
}
