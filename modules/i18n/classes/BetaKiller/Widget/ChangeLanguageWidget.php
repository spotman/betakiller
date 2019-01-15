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

        $currentCode  = '';
        $currentLabel = '';
        $links        = [];

        foreach ($this->languageRepo->getAppLanguages() as $lang) {
            $params = $urlHelper->createUrlContainer()->setEntity($lang);
            $url    = $urlHelper->makeUrl($element, $params, false);

            if ($currentQuery) {
                $url .= '?'.$currentQuery;
            }

            $data = [
                'url'   => $url,
                'label' => $lang->getLabel(),
            ];

            if ($lang->getIsoCode() === $currentLang->getIsoCode()) {
                \array_unshift($links, $data);
                $currentCode  = $currentLang->getIsoCode();
                $currentLabel = $currentLang->getLabel();
            } else {
                $links[] = $data;
            }
        }

        return [
            'current_code'  => $currentCode,
            'current_label' => $currentLabel,
            'lang_list'     => $links,
        ];
    }
}
