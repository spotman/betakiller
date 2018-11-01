<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Config\ConfigProviderInterface;
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
        foreach ($this->config->load(['roles']) as $roleCodename) {
            $roleModel = $this->roleRepo->findByName($roleCodename);

            // Add missing role and keep existing untouched
            if (!$roleModel) {
                $roleModel = $this->roleRepo->create()->setName($roleCodename);
                $this->roleRepo->save($roleModel);

                $this->logger->info('Role ":role" added', [
                    ':role' => $roleCodename,
                ]);
            }
        }
    }
}
