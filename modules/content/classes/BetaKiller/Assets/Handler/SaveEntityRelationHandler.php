<?php
namespace BetaKiller\Assets\Handler;

use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Repository\EntityRepository;

class SaveEntityRelationHandler implements AssetsHandlerInterface
{
    const CODENAME = 'SaveEntityRelation';

    /**
     * @var \BetaKiller\Repository\EntityRepository
     */
    private $entityRepository;

    /**
     * SaveEntityRelationHandler constructor.
     *
     * @param \BetaKiller\Repository\EntityRepository $entityRepository
     */
    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface $provider
     * @param \BetaKiller\Model\EntityModelRelatedInterface       $model
     * @param array                                               $postData
     */
    public function update(AssetsProviderInterface $provider, $model, array $postData): void
    {
        $entityID     = (int)($postData['entityID'] ?? null);
        $entityItemID = (int)($postData['entityItemID'] ?? null);

        if ($entityID) {
            $entity = $this->entityRepository->findById($entityID);
            $model->setEntity($entity);
        }

        if ($entityItemID) {
            $model->setEntityItemID($entityItemID);
        }
    }
}
