<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Auth\RoleConfig;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Repository\RoleRepository;
use Psr\Log\LoggerInterface;

class ImportRoles extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ImportRoles constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \BetaKiller\Repository\RoleRepository      $roleRepo
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        ConfigProviderInterface $config,
        RoleRepository $roleRepo,
        LoggerInterface $logger
    ) {
        $this->config   = $config;
        $this->roleRepo = $roleRepo;
        $this->logger   = $logger;

        parent::__construct();
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $rolesConfig = $this->config->load([RoleConfig::CONFIG_GROUP_NAME]);

        foreach ($rolesConfig as $name => $options) {
            $model = $this->roleRepo->findByName($name);

            $description = $options[RoleConfig::OPTION_DESC];

            // Add missing role and keep existing untouched
            if (!$model) {
                $model = $this->roleRepo->create()
                    ->setName($name)
                    ->setDescription($description);

                $this->logger->info('Role ":role" added', [
                    ':role' => $name,
                ]);
            }

            // Add description if not exists
            if (!$model->getDescription()) {
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

            /** @var RoleInterface $parentRole */
            foreach ($model->getParents() as $parentRole) {
                if (!\in_array($parentRole->getName(), $inherits, true)) {
                    // Unused parent role, remove it
                    $model->removeParent($parentRole);

                    $this->logger->info('Role ":role" parent ":parent" removed', [
                        ':role'   => $name,
                        ':parent' => $parentRole->getName(),
                    ]);
                }
            }

            // Process inherits
            foreach ($inherits as $inheritName) {
                $inheritModel = $this->roleRepo->getByName($inheritName);

                if (!$model->hasParent($inheritModel)) {
                    $model->addParent($inheritModel);
                    $this->logger->info('Role ":role" parent ":parent" added', [
                        ':role'   => $name,
                        ':parent' => $inheritName,
                    ]);
                }
            }
        }
    }
}
