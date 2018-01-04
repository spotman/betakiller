<?php
namespace BetaKiller\Assets\Handler;

use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Repository\EntityRepository;

class SaveEntityItemRelationHandler implements AssetsHandlerInterface
{
    public const CODENAME = 'SaveEntityItemRelation';

    /**
     * @var \BetaKiller\Repository\EntityRepository
     */
    private $entityRepository;

    /**
     * SaveEntityItemRelationHandler constructor.
     *
     * @param \BetaKiller\Repository\EntityRepository $entityRepository
     */
    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface $provider
     * @param \BetaKiller\Model\EntityItemRelatedInterface        $model
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
