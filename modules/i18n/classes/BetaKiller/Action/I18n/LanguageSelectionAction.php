<?php
declare(strict_types=1);

namespace BetaKiller\Action\I18n;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Middleware\I18nMiddleware;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LanguageSelectionAction extends AbstractAction
{
    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18NFacade;

    /**
     * @var \BetaKiller\Repository\LanguageRepository
     */
    private $languageRepo;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @param \BetaKiller\I18n\I18nFacade               $i18NFacade
     * @param \BetaKiller\Repository\LanguageRepository $languageRepo
     * @param \BetaKiller\Repository\UserRepository     $userRepo
     */
    public function __construct(
        I18nFacade $i18NFacade,
        LanguageRepository $languageRepo,
        UserRepository $userRepo
    ) {
        $this->i18NFacade   = $i18NFacade;
        $this->languageRepo = $languageRepo;
        $this->userRepo     = $userRepo;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $postData = ServerRequestHelper::getPost($request);

        $langName = $postData['lang_name'] ?? null;
        $langName = strtolower(trim($langName));

        if (!$langName) {
            throw new BadRequestHttpException('Not found language name.');
        }

        $response = ResponseHelper::successJson();

        if (!ServerRequestHelper::isGuest($request)) {
            $this->setLangUserProfile($request, $langName);
        }

        return $this->setLangCookie($response, $langName);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string                                   $langName
     *
     * @return \BetaKiller\Action\LanguageSelectionAction
     */
    public function setLangUserProfile(ServerRequestInterface $request, string $langName): self
    {
        $langModel = $this->languageRepo->getByName($langName);
        $userModel = ServerRequestHelper::getUser($request);
        $userModel->setLanguage($langModel);
        $this->userRepo->save($userModel);

        return $this;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string                              $langName
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception
     */
    public function setLangCookie(ResponseInterface $response, string $langName): ResponseInterface
    {
        $i18n = new I18nHelper($this->i18NFacade);
        $i18n->setLang($langName);

        $cookieName   = I18nMiddleware::COOKIE_NAME;
        $dateInterval = I18nMiddleware::DATE_INTERVAL;

        return ResponseHelper::setCookie($response, $cookieName, $langName, new \DateInterval($dateInterval));
    }
}
