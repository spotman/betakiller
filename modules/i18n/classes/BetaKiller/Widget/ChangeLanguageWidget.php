<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Action\ChangeLanguageAction;
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
        $urlHelper        = ServerRequestHelper::getUrlHelper($request);
        $actionUrlElement = $urlHelper->getUrlElementByCodename(
            ChangeLanguageAction::codename()
        );
        $actionUrl        = $urlHelper->makeUrl($actionUrlElement);
        $i18n             = ServerRequestHelper::getI18n($request);
        $langNameActive   = $i18n->getLang();
        $langsName        = $this->getLangsName();

        return [
            'action_url'       => $actionUrl,
            'lang_name_active' => $langNameActive,
            'langs_name'       => $langsName,
        ];
    }

    /**
     * @return string[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getLangsName(): array
    {
        $langsName  = [];
        $langsModel = $this->languageRepo->getAll();
        foreach ($langsModel as $langModel) {
            $langsName[] = $langModel->getName();
        }

        return $langsName;
    }
}
