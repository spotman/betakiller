<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\I18n;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\I18nModelInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationRepository;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

class CommonIndexIFace extends AbstractAdminIFace
{
    /**
     * @var \BetaKiller\Repository\TranslationRepository
     */
    private $i18nRepo;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * CommonIndexIFace constructor.
     *
     * @param \BetaKiller\Repository\TranslationRepository       $i18nRepo
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     */
    public function __construct(TranslationRepository $i18nRepo, LanguageRepositoryInterface $langRepo)
    {
        $this->i18nRepo = $i18nRepo;
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

        foreach ($this->i18nRepo->findEmptyItems($languages) as $emptyItem) {
            $data[] = $this->formatItem($emptyItem, $helper);
        }

        return $data;
    }

    private function formatItem(I18nModelInterface $model, UrlHelper $helper): array
    {
        $key = $model->getKey();

        return [
            'key'   => $key->getI18nKey(),
            'value' => $model->getValue(),
            'url'   => $helper->getReadEntityUrl($key, ZoneInterface::ADMIN),
        ];
    }
}
