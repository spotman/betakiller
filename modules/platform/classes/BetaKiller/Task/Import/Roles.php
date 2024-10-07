<?php

declare(strict_types=1);

namespace BetaKiller\Task\Import;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Config\RoleConfig;
use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Exception\DomainException;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Model\Role;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class Roles extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\RoleRepositoryInterface
     */
    private $roleRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Factory\EntityFactoryInterface
     */
    private $entityFactory;

    /**
     * Roles constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface     $config
     * @param \BetaKiller\Repository\RoleRepositoryInterface $roleRepo
     * @param \BetaKiller\Factory\EntityFactoryInterface     $entityFactory
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(
        ConfigProviderInterface $config,
        RoleRepositoryInterface $roleRepo,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger
    ) {
        $this->config        = $config;
        $this->roleRepo      = $roleRepo;
        $this->entityFactory = $entityFactory;
        $this->logger        = $logger;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @return array
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $rolesConfig = $this->config->load(RoleConfig::CONFIG_GROUP_NAME, []);

        foreach ($rolesConfig as $name => $options) {
            $model = $this->roleRepo->findByName($name);

            $description = $options[RoleConfig::OPTION_DESC];

            // Add missing role and keep existing untouched
            if (!$model) {
                /** @var RoleInterface $model */
                $model = $this->entityFactory->create(Role::getModelName());

                $model
                    ->setName($name)
                    ->setDescription($description);

                $this->logger->info('Role ":role" added', [
                    ':role' => $name,
                ]);
            }

            // Update description
            if ($model->getDescription() !== $description) {
                $model->setDescription($description);
                $this->logger->info('Role ":role" description updated', [
                    ':role' => $name,
                ]);
            }

            $this->roleRepo->save($model);
        }

        // Process inheritance on already existing models
        foreach ($rolesConfig as $name => $options) {
            $model = $this->roleRepo->findByName($name);

            $inherits = $options[RoleConfig::OPTION_INHERITS] ?? [];

            // Prevent guest role to be inherited by any other role
            if (\in_array(RoleInterface::GUEST, $inherits, true)) {
                throw new DomainException('Role ":name" must not inherit ":guest" role', [
                    ':name'  => $name,
                    ':guest' => RoleInterface::GUEST,
                ]);
            }

            /** @var RoleInterface $parentRole */
            foreach ($this->roleRepo->getParents($model) as $parentRole) {
                if (!\in_array($parentRole->getName(), $inherits, true)) {
                    // Unused parent role, remove it
                    $this->roleRepo->removeParent($model, $parentRole);

                    $this->logger->info('Role ":role" parent ":parent" removed', [
                        ':role'   => $name,
                        ':parent' => $parentRole->getName(),
                    ]);
                }
            }

            // Process inherits
            foreach ($inherits as $inheritName) {
                $inheritModel = $this->roleRepo->getByName($inheritName);

                if (!$this->roleRepo->hasParent($model, $inheritModel)) {
                    $this->roleRepo->addParent($model, $inheritModel);
                    $this->logger->info('Role ":role" parent ":parent" added', [
                        ':role'   => $name,
                        ':parent' => $inheritName,
                    ]);
                }
            }
        }
    }
}
