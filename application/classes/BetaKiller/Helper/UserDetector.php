<?php
namespace BetaKiller\Helper;

use Auth;
use BetaKiller\Exception;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Service\UserService;
use BetaKiller\Task\AbstractTask;
use Zend\Expressive\Session\SessionInterface;

class UserDetector
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
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
    private $userService;

    /**
     * UserDetector constructor.
     *
     * @param \BetaKiller\Service\UserService       $userService
     * @param \BetaKiller\Helper\AppEnvInterface    $appEnv
     * @param \Auth                                 $auth
     * @param \BetaKiller\Repository\UserRepository $repo
     * @param \BetaKiller\Helper\I18nHelper         $i18n
     */
    public function __construct(
        UserService $userService,
        AppEnvInterface $appEnv,
        Auth $auth,
        UserRepository $repo,
        I18nHelper $i18n
    ) {
        $this->appEnv      = $appEnv;
        $this->auth        = $auth;
        $this->repository  = $repo;
        $this->i18n        = $i18n;
        $this->userService = $userService;
    }

    public function fromSession(SessionInterface $session): UserInterface
    {
        return $session->get('auth_user') ?: $this->userService->createGuest();
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
            $user = $this->userService->createGuest();
        }

        $this->setSystemLanguage($user);

        if ($this->userService->isDeveloper($user)) {
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
        // Get username from CLI arguments or use default instead
        $userName = $this->appEnv->getCliOption('user') ?: AbstractTask::CLI_USER_NAME;

        $user = $this->repository->searchBy($userName);

        if (!$user) {
            throw new Exception('Missing CLI user ":name", install it with CreateCliUser task', [
                ':name' => $userName,
            ]);
        }

        return $user;
    }

    private function setSystemLanguage(UserInterface $user): void
    {
        $this->i18n->initFromUser($user);
    }
}
