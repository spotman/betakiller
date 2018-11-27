<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\I18n;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\TranslationKeyModelInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationKeyRepository;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

class CommonIndexIFace extends AbstractAdminIFace
{
    /**
     * @var \BetaKiller\Repository\TranslationKeyRepository
     */
    private $keyRepo;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * CommonIndexIFace constructor.
     *
     * @param \BetaKiller\Repository\TranslationKeyRepository    $keyRepo
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     */
    public function __construct(TranslationKeyRepository $keyRepo, LanguageRepositoryInterface $langRepo)
    {
        $this->keyRepo  = $keyRepo;
        $this->langRepo = $langRepo;
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

        $filterLang = ServerRequestHelper::getQueryPart($request, 'filter');

        $allLanguages = $this->langRepo->getAll();

        // TODO Allow filtering for one language (fix sql and logic)
        if ($filterLang) {
            $filterModel = $this->langRepo->findByName($filterLang);

            if (!$filterModel) {
                throw new BadRequestHttpException;
            }

            $filterLanguages = [$filterModel];
        } else {
            $filterLanguages = $allLanguages;
        }

        $langList = [];

        foreach ($allLanguages as $lang) {
            $langName            = $lang->getName();
            $langList[$langName] = [
                'label' => $lang->getLabel(),
                'name'  => $lang->getName(),
                'url'   => (string)$currentUri->withQuery('?filter='.$langName),
            ];
        }

        return [
            'filter'      => [
                'self'    => (string)$currentUri->withQuery(''),
                'current' => $filterLang,
                'list'    => $langList,
            ],
            'empty_items' => $this->getEmptyItems($urlHelper, $filterLanguages),
        ];
    }

    private function getEmptyItems(UrlHelper $helper, array $languages): array
    {
        $data = [];

        foreach ($this->keyRepo->findKeysWithEmptyValues($languages) as $emptyItem) {
            $data[] = $this->formatItem($emptyItem, $helper);
        }

        return $data;
    }

    private function formatItem(TranslationKeyModelInterface $model, UrlHelper $helper): array
    {
        return [
            'key'   => $model->getI18nKeyName(),
            'value' => $model->getAnyI18nValue(),
            'url'   => $helper->getReadEntityUrl($model, ZoneInterface::ADMIN),
        ];
    }
}
