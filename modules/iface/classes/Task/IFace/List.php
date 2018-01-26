<?php
declare(strict_types=1);

use BetaKiller\Task\AbstractTask;

class Task_IFace_List extends AbstractTask
{
    /**
     * @Inject
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * @param array $params
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    protected function _execute(array $params)
    {
        $iterator = $this->tree->getRecursiveIteratorIterator();

        foreach ($iterator as $model) {
            $this->logger->info('[:zone] :codename', [
                ':zone'     => $model->getZoneName(),
                ':codename' => $model->getCodename(),
            ]);
        }
    }
}
