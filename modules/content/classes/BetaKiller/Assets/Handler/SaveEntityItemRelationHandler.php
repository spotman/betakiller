<?php
namespace BetaKiller\Assets\Handler;

use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Model\EntityItemRelatedInterface;
use BetaKiller\Model\UserInterface;
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
     * @param \BetaKiller\Model\UserInterface                     $user
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function update(AssetsProviderInterface $provider, $model, array $postData, UserInterface $user): void
    {
        if (!$model instanceof EntityItemRelatedInterface) {
            throw new AssetsException('Assets model ":name" must implement :must', [
                ':name' => $provider->getCodename(),
                ':must' => EntityItemRelatedInterface::class,
            ]);
        }

        $entitySlug   = (string)($postData['entitySlug'] ?? null);
        $entityItemID = (int)($postData['entityItemID'] ?? null);

        if ($entitySlug) {
            $entity = $this->entityRepository->findBySlug($entitySlug);
            $model->setEntity($entity);
        }

        if ($entityItemID) {
            $model->setEntityItemID($entityItemID);
        }
    }
}
