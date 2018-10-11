<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\WebHookModelInterface;
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

        parent::__construct();
    }

    public function defineOptions(): array
    {
        // No cli arguments
        return [];
    }

    /**
     *
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function run(): void
    {
        foreach ($this->tree->getRecursiveIteratorIterator() as $model) {
            switch (true) {
                case $model instanceof IFaceModelInterface:
                    $this->logger->info('[IFace] [:zone] :codename', [
                        ':zone'     => $model->getZoneName(),
                        ':codename' => $model->getCodename(),
                    ]);
                    break;

                case $model instanceof WebHookModelInterface:
                    $this->logger->info('[WebHook] :codename', [
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
