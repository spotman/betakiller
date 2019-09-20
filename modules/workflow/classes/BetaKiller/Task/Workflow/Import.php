<?php
declare(strict_types=1);

namespace BetaKiller\Task\Workflow;

use BetaKiller\Config\WorkflowConfigInterface;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Factory\RepositoryFactory;
use BetaKiller\Repository\WorkflowStateRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Workflow\WorkflowStateException;
use BetaKiller\Workflow\WorkflowStateInterface;
use Psr\Log\LoggerInterface;

class Import extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\WorkflowConfigInterface
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Factory\RepositoryFactory
     */
    private $repoFactory;

    /**
     * @var \BetaKiller\Factory\EntityFactoryInterface
     */
    private $entityFactory;

    /**
     * Import constructor.
     *
     * @param \BetaKiller\Config\WorkflowConfigInterface $config
     * @param \BetaKiller\Factory\RepositoryFactory      $repoFactory
     * @param \BetaKiller\Factory\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        WorkflowConfigInterface $config,
        RepositoryFactory $repoFactory,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->config        = $config;
        $this->logger        = $logger;
        $this->repoFactory   = $repoFactory;
        $this->entityFactory = $entityFactory;
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
        foreach ($this->config->getModels() as $modelName) {
            $this->logger->debug('Processing model ":name"', [
                ':name' => $modelName,
            ]);

            // Get state repo
            $stateModelName = $this->config->getStateModelName($modelName);
            $stateRepo      = $this->repoFactory->create($stateModelName);

            if (!$stateRepo instanceof WorkflowStateRepositoryInterface) {
                throw new WorkflowStateException('Repo ":name" must implement :class', [
                    ':name'  => $modelName,
                    ':class' => WorkflowStateRepositoryInterface::class,
                ]);
            }

            $configStates = $this->config->getStates($modelName);

            // Add new and update flags
            foreach ($configStates as $stateName) {
                $state = $stateRepo->findByCodename($stateName);

                if (!$state) {
                    $state = $this->entityFactory->create($stateModelName);

                    if (!$state instanceof WorkflowStateInterface) {
                        throw new WorkflowStateException('Entity ":name" must implement :class', [
                            ':name'  => $stateName,
                            ':class' => WorkflowStateInterface::class,
                        ]);
                    }

                    $state->setCodename($stateName);
                }

                // Update flags
                switch (true) {
                    case $this->config->isStartState($modelName, $stateName):
                        $this->logger->debug('Start from ":name" state', [
                            ':name' => $stateName,
                        ]);

                        $state->markAsStart();
                        break;

                    case $this->config->isFinishState($modelName, $stateName):
                        $this->logger->debug('Finish at ":name" state', [
                            ':name' => $stateName,
                        ]);

                        $state->markAsFinish();
                        break;

                    default:
                        $state->markAsRegular();
                }

                $stateRepo->save($state);

                $this->logger->debug('State ":name" imported', [
                    ':name' => $stateName,
                ]);
            }

            // Remove unused states
            foreach ($stateRepo->getAll() as $existingState) {
                $stateName = $existingState->getCodename();

                if (!in_array($stateName, $configStates, true)) {
                    $this->logger->debug('Removing unused state ":name"', [
                        ':name' => $stateName,
                    ]);

                    $stateRepo->delete($existingState);
                }
            }
        }
    }
}
