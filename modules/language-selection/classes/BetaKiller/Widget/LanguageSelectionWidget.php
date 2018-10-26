<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Repository\LanguageRepository;
use Psr\Http\Message\ServerRequestInterface;

class LanguageSelectionWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Repository\LanguageRepository
     */
    private $languageRepo;

    /**
     * @param \BetaKiller\Helper\UrlHelper              $urlHelper
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
     * @throws \BetaKiller\Widget\WidgetException
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $urlHelper        = ServerRequestHelper::getUrlHelper($request);
        $actionUrlElement = $urlHelper->getUrlElementByCodename('LanguageSelection');
        $actionUrl        = $urlHelper->makeUrl($actionUrlElement);
        $i18n             = ServerRequestHelper::getI18n($request);
        $langCodeActive   = $i18n->getLang();
        $langsCode        = $this->getLangsCode();

        return [
            'action_url'       => $actionUrl,
            'lang_code_active' => $langCodeActive,
            'langs_code'       => $langsCode,
        ];
    }

    /**
     * @return string[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getLangsCode(): array
    {
        $langsCode  = [];
        $langsModel = $this->languageRepo->getAll();
        foreach ($langsModel as $langModel) {
            $langsCode[] = $langModel->getName();
        }

        return $langsCode;
    }
}
