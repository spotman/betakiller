<?php
namespace BetaKiller\Helper;

use Auth;
use BetaKiller\Exception;
use BetaKiller\Model\GuestUser;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Service\UserService;
use BetaKiller\Task\AbstractTask;

class UserDetector
{
    /**
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @var \Auth
     */
    private $auth;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $repository;

    /**
     * @var \BetaKiller\Helper\I18nHelper
     */
    private $i18n;

    /**
     * @var \BetaKiller\Service\UserService
     */
    private $service;

    /**
     * UserDetector constructor.
     *
     * @param \BetaKiller\Service\UserService       $service
     * @param \BetaKiller\Helper\AppEnv             $appEnv
     * @param \Auth                                 $auth
     * @param \BetaKiller\Repository\UserRepository $repo
     * @param \BetaKiller\Helper\I18nHelper         $i18n
     */
    public function __construct(UserService $service, AppEnv $appEnv, Auth $auth, UserRepository $repo, I18nHelper $i18n)
    {
        $this->appEnv     = $appEnv;
        $this->auth       = $auth;
        $this->repository = $repo;
        $this->i18n       = $i18n;
        $this->service    = $service;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception
     */
    public function detect(): UserInterface
    {
        /** @var UserInterface|null $user */
        $user = $this->appEnv->isCLI()
            ? $this->detectCliUser()
            : $this->auth->get_user();


        if (!$user) {
            $user = new GuestUser();
        }

        $this->setSystemLanguage($user);

        if ($this->service->isDeveloper($user)) {
            $this->appEnv->enableDebug();
        }

        return $user;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception
     */
    private function detectCliUser(): UserInterface
    {
        $user = $this->repository->searchBy(AbstractTask::CLI_USER_NAME);

        if (!$user) {
            throw new Exception('Missing CLI user, install it with CreateCliUser task');
        }

        return $user;
    }

    private function setSystemLanguage(UserInterface $user): void
    {
        $this->i18n->initFromUser($user);
    }
}
