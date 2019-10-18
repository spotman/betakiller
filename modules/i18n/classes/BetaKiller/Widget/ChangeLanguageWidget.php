<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Action\App\I18n\ChangeUserLanguageAction;
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
        $stack       = ServerRequestHelper::getUrlElementStack($request);
        $urlHelper   = ServerRequestHelper::getUrlHelper($request);
        $currentLang = ServerRequestHelper::getI18n($request)->getLang();

        // Link to current page in other language
        $element = $stack->hasCurrent()
            ? $stack->getCurrent()
            : $urlHelper->getDefaultUrlElement();

        $links = [];

        foreach ($this->languageRepo->getAppLanguages() as $lang) {
            $params = $urlHelper->createUrlContainer()->setEntity($lang);
            $url    = $urlHelper->makeUrl($element, $params, false);

            $data = [
                'url'   => $url,
                'code'  => $lang->getIsoCode(),
                'label' => $lang->getLabel(),
            ];

            $links[$lang->getIsoCode()] = $data;
        }

        $changeAction = $urlHelper->getUrlElementByCodename(ChangeUserLanguageAction::codename());

        return [
            'current'   => $currentLang->getIsoCode(),
            'lang_list' => $links,

            'action_url'      => $urlHelper->makeUrl($changeAction),
            'action_key_lang' => ChangeUserLanguageAction::ARG_LANG,
            'action_key_url'  => ChangeUserLanguageAction::ARG_URL,
        ];
    }
}
