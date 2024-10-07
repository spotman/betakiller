<?php

declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Url\ActionModelInterface;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementTreeInterface;
use Psr\Log\LoggerInterface;

class IFaceList extends AbstractTask
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Task_IFace_IFaceList constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \Psr\Log\LoggerInterface                $logger
     */
    public function __construct(UrlElementTreeInterface $tree, LoggerInterface $logger)
    {
        $this->tree   = $tree;
        $this->logger = $logger;
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        // No cli arguments
        return [];
    }

    /**
     *
     * @param \BetaKiller\Console\ConsoleInputInterface $params *
     *
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function run(ConsoleInputInterface $params): void
    {
        foreach ($this->tree->getRecursiveIteratorIterator() as $model) {
            switch (true) {
                case $model instanceof IFaceModelInterface:
                    $this->logger->info('[:zone] [IFace] :codename', [
                        ':zone'     => $model->getZoneName(),
                        ':codename' => $model->getCodename(),
                    ]);
                    break;

                case $model instanceof ActionModelInterface:
                    $this->logger->info('[:zone] [Action] :codename', [
                        ':zone'     => $model->getZoneName(),
                        ':codename' => $model->getCodename(),
                    ]);
                    break;

                case $model instanceof DummyModelInterface:
                    $this->logger->info('[Dummy] :codename', [
                        ':codename' => $model->getCodename(),
                    ]);
                    break;

                default:
                    throw new UrlElementException('Unknown UrlElement :class', [
                        ':class' => \get_class($model),
                    ]);
            }
        }
    }
}
