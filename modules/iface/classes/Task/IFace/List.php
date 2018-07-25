<?php
declare(strict_types=1);

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\WebHookModelInterface;

class Task_IFace_List extends AbstractTask
{
    /**
     * @Inject
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @param array $params
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    protected function _execute(array $params)
    {
        foreach ($this->tree->getRecursiveIteratorIterator() as $model) {
            if ($model instanceof IFaceModelInterface) {
                $this->logger->info('[IFace] [:zone] :codename', [
                    ':zone'     => $model->getZoneName(),
                    ':codename' => $model->getCodename(),
                ]);
            } else if ($model instanceof WebHookModelInterface) {
                $this->logger->info('[WebHook] :codename', [
                    ':codename' => $model->getCodename(),
                ]);

            } else if ($model instanceof DummyModelInterface) {
                $this->logger->info('[Dummy] :codename', [
                    ':codename' => $model->getCodename(),
                ]);
            } else {
                throw new IFaceException('Unknown UrlElement :class', [':class' => \get_class($model)]);
            }
        }
    }
}
