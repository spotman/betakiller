<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Middleware\I18nMiddleware;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChangeLanguageAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Repository\LanguageRepository
     */
    private $languageRepo;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $cookies;

    /**
     * @param \BetaKiller\Repository\LanguageRepository $languageRepo
     * @param \BetaKiller\Repository\UserRepository     $userRepo
     * @param \BetaKiller\Helper\CookieHelper           $cookies
     */
    public function __construct(
        LanguageRepository $languageRepo,
        UserRepository $userRepo,
        CookieHelper $cookies
    ) {
        $this->languageRepo = $languageRepo;
        $this->userRepo     = $userRepo;
        $this->cookies      = $cookies;
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
     * @return \BetaKiller\Action\ChangeLanguageAction
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
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
     * @throws \Exception
     */
    public function setLangCookie(ResponseInterface $response, string $langName): ResponseInterface
    {
        $cookieName   = I18nMiddleware::COOKIE_NAME;
        $dateInterval = I18nMiddleware::COOKIE_DATE_INTERVAL;

        return $this->cookies->set($response, $cookieName, $langName, new \DateInterval($dateInterval));
    }
}
