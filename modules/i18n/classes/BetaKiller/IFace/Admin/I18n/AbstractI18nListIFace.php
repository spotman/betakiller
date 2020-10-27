<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\I18n;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\TextHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\I18n\I18nConfigInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Repository\I18nKeyRepositoryInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

abstract class AbstractI18nListIFace extends AbstractAdminIFace
{
    /**
     * @var \BetaKiller\Repository\I18nKeyRepositoryInterface
     */
    private I18nKeyRepositoryInterface $keyRepo;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private LanguageRepositoryInterface $langRepo;

    /**
     * @var \BetaKiller\I18n\I18nConfigInterface
     */
    private I18nConfigInterface $i18nConfig;

    /**
     * CommonListIFace constructor.
     *
     * @param \BetaKiller\Repository\I18nKeyRepositoryInterface  $keyRepo
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     * @param \BetaKiller\I18n\I18nConfigInterface               $i18nConfig
     */
    public function __construct(
        I18nKeyRepositoryInterface $keyRepo,
        LanguageRepositoryInterface $langRepo,
        I18nConfigInterface $i18nConfig
    ) {
        $this->keyRepo    = $keyRepo;
        $this->langRepo   = $langRepo;
        $this->i18nConfig = $i18nConfig;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper  = ServerRequestHelper::getUrlHelper($request);
        $currentUri = $request->getUri();

        $filterLangName = ServerRequestHelper::getQueryPart($request, 'filter');

        // Allow filtering for one language
        $filterLang = $filterLangName
            ? $this->langRepo->findByIsoCode($filterLangName)
            : null;

        return [
            'filter' => [
                'self'    => (string)$currentUri->withQuery(''),
                'current' => $filterLangName,
                'list'    => $this->getLangList($currentUri),
            ],
            'items'  => $this->getItems($urlHelper, $filterLang),
        ];
    }

    private function getLangList(UriInterface $currentUri): array
    {
        $langList = [];

        foreach ($this->langRepo->getAppLanguages(true) as $lang) {
            $langName = $lang->getIsoCode();

            $langList[$langName] = [
                'label' => $lang->getLabel(),
                'name'  => $lang->getIsoCode(),
                'url'   => (string)$currentUri->withQuery('?filter='.$langName),
            ];
        }

        return $langList;
    }

    private function getItems(UrlHelperInterface $helper, ?LanguageInterface $filterLang): array
    {
        $data = [];

        $items = $filterLang
            ? $this->keyRepo->findKeysWithEmptyValues($filterLang)
            : $this->keyRepo->getAllI18nKeys();

        $fallbackOnlyKeys = $this->i18nConfig->getFallbackOnlyKeys();

        foreach ($items as $emptyItem) {
            $keyName = $emptyItem->getI18nKeyName();

            // Skip fallback-only keys in filtered mode
            if ($filterLang) {
                foreach ($fallbackOnlyKeys as $fallbackOnlyKey) {
                    if (TextHelper::startsWith($keyName, $fallbackOnlyKey)) {
                        continue 2;
                    }
                }
            }

            $data[] = $this->formatItem($emptyItem, $helper);
        }

        return $data;
    }

    private function formatItem(I18nKeyModelInterface $model, UrlHelperInterface $helper): array
    {
        return [
            'key'   => $model->getI18nKeyName(),
            'value' => $model->hasAnyI18nValue() ? $model->getAnyI18nValue() : null,
            'url'   => $helper->getReadEntityUrl($model, ZoneInterface::ADMIN),
        ];
    }
}
