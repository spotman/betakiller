<?php

declare(strict_types=1);

namespace BetaKiller\Console;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;
use BetaKiller\Factory\UserInfo;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\WithCliUserInterface;
use BetaKiller\Workflow\UserWorkflow;

class ConsoleTaskFactory implements ConsoleTaskFactoryInterface
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * ConsoleTaskFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     * @param \BetaKiller\Env\AppEnvInterface                           $appEnv
     * @param \BetaKiller\Repository\UserRepositoryInterface            $userRepo
     * @param \BetaKiller\Factory\GuestUserFactory                      $guestFactory
     * @param \BetaKiller\Config\AppConfigInterface                     $appConfig
     * @param \BetaKiller\Workflow\UserWorkflow                         $userWorkflow
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(
        NamespaceBasedFactoryBuilderInterface $factoryBuilder,
        private readonly AppEnvInterface $appEnv,
        private readonly UserRepositoryInterface $userRepo,
        private readonly GuestUserFactory $guestFactory,
        private readonly AppConfigInterface $appConfig,
        private readonly UserWorkflow $userWorkflow
    ) {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setExpectedInterface(ConsoleTaskInterface::class)
            ->prepareArgumentsWith(function (?array $arguments, string $className): array {
                $arguments ??= [];

                if (is_a($className, WithCliUserInterface::class, true)) {
                    $arguments['user'] = $this->detectCliUser() ?? $this->createCliUser();
                }

                return $arguments;
            });
    }

    /**
     * @param string $className
     *
     * @return \BetaKiller\Console\ConsoleTaskInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $className): ConsoleTaskInterface
    {
        return $this->factory->create($className);
    }

    private function detectCliUser(): ?UserInterface
    {
        // Get username from CLI arguments or use default instead
        $userName = $this->appEnv->getCliOption(AppEnvInterface::CLI_OPTION_USER) ?: User::CLI_USERNAME;

        if ($userName === 'guest') {
            return $this->guestFactory->create();
        }

        return $this->userRepo->findByUsername($userName);
    }

    private function createCliUser(): UserInterface
    {
        $userName = User::CLI_USERNAME;

        $host  = $this->appConfig->getBaseUri()->getHost();
        $email = $userName.'@'.$host;

        $user = $this->userWorkflow->create(
            new UserInfo(
                User::DEFAULT_IP,
                $email,
                null,
                $userName,
                null,
                RoleInterface::CLI
            )
        );

        // No notification for cron user
        $user->disableEmailNotification();
        $this->userRepo->save($user);

        return $user;
    }
}
