<?php

declare(strict_types=1);

namespace BetaKiller\Task\Workflow;

use BetaKiller\Config\WorkflowConfigInterface;
use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Factory\RepositoryFactoryInterface;
use BetaKiller\Model\WorkflowStateModelInterface;
use BetaKiller\Repository\WorkflowStateDbRepositoryInterface;
use BetaKiller\Repository\WorkflowStateRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Workflow\WorkflowStateException;
use Psr\Log\LoggerInterface;

class Import extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\WorkflowConfigInterface
     */
    private WorkflowConfigInterface $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\Factory\RepositoryFactoryInterface
     */
    private RepositoryFactoryInterface $repoFactory;

    /**
     * @var \BetaKiller\Factory\EntityFactoryInterface
     */
    private EntityFactoryInterface $entityFactory;

    /**
     * Import constructor.
     *
     * @param \BetaKiller\Config\WorkflowConfigInterface     $config
     * @param \BetaKiller\Factory\RepositoryFactoryInterface $repoFactory
     * @param \BetaKiller\Factory\EntityFactoryInterface     $entityFactory
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(
        WorkflowConfigInterface $config,
        RepositoryFactoryInterface $repoFactory,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger
    ) {
        $this->config        = $config;
        $this->logger        = $logger;
        $this->repoFactory   = $repoFactory;
        $this->entityFactory = $entityFactory;
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
        foreach ($this->config->getModels() as $modelName) {
            $this->logger->debug('Processing model ":name"', [
                ':name' => $modelName,
            ]);

            $this->proceedModel($modelName);
        }
    }

    private function proceedModel(string $modelName): void
    {
        // Get state repo
        $stateModelName = $this->config->getStateModelName($modelName);
        $stateRepo      = $this->repoFactory->create($stateModelName);

        if (!$stateRepo instanceof WorkflowStateRepositoryInterface) {
            throw new WorkflowStateException('Repo ":name" must implement :class', [
                ':name'  => $modelName,
                ':class' => WorkflowStateRepositoryInterface::class,
            ]);
        }

        if (!$stateRepo instanceof WorkflowStateDbRepositoryInterface) {
            $this->logger->debug('Model :name is not stored in DB, skipping', [
                ':name' => $modelName,
            ]);

            return;
        }

        $configStates = $this->config->getStates($modelName);

        // Add new and update flags
        foreach ($configStates as $stateName) {
            $state = $stateRepo->findByCodename($stateName);

            $state ??= $this->entityFactory->create($stateModelName);

            if (!$state instanceof WorkflowStateModelInterface) {
                throw new WorkflowStateException('Entity ":name" must implement :class', [
                    ':name'  => $stateName,
                    ':class' => WorkflowStateModelInterface::class,
                ]);
            }

            $state->setCodename($stateName);

            /** @noinspection OneTimeUseVariablesInspection */
            $transitions = $this->config->getStateTargetTransitions($modelName, $stateName);

            // Check state has transitions defined
            foreach ($transitions as $transitionName => $transitionTarget) {
                $this->logger->debug('[:source]--- :name --->[:target] is allowed to: :roles', [
                    ':name'   => $transitionName,
                    ':source' => $stateName,
                    ':target' => $transitionTarget,
                    ':roles'  => implode(', ', $this->config->getTransitionRoles($modelName, $transitionName)),
                ]);
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

        /** @var \BetaKiller\Repository\HasWorkflowStateRepositoryInterface $modelRepo */
        $modelRepo = $this->repoFactory->create($modelName);

        $startState = $stateRepo->getStartState();

        // Fetch models without state and preset start state
        foreach ($modelRepo->getAllMissingState() as $model) {
            $model->initWorkflowState($startState);
            $modelRepo->save($model);
        }
    }
}
