<?php
declare(strict_types=1);

namespace BetaKiller\Action\App\I18n;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Action\PostRequestActionInterface;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class ChangeUserLanguageAction extends AbstractAction implements PostRequestActionInterface
{
    public const ARG_LANG = 'lang';
    public const ARG_URL  = 'url';

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * ApplicantOneIFace constructor.
     *
     * @param \BetaKiller\Repository\UserRepository              $userRepo
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $languageRepo
     */
    public function __construct(UserRepository $userRepo, LanguageRepositoryInterface $languageRepo)
    {
        $this->userRepo = $userRepo;
        $this->langRepo = $languageRepo;
    }

    /**
     * Arguments definition for request` POST data
     *
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     */
    public function definePostArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_LANG)
            ->string(self::ARG_URL);
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!ServerRequestHelper::isAjax($request)) {
            throw new BadRequestHttpException;
        }

        $arguments = ActionRequestHelper::postArguments($request);
        $langCode  = $arguments->getString(self::ARG_LANG);
        $url       = $arguments->getString(self::ARG_URL);

        $user = ServerRequestHelper::getUser($request);

        // Update user lang after switching to another language
        if (!$user->isGuest()) {
            $lang = $this->langRepo->getByIsoCode($langCode);

            $user->setLanguage($lang);
            $this->userRepo->save($user);
        }

        return ResponseHelper::successJson($url);
    }
}
