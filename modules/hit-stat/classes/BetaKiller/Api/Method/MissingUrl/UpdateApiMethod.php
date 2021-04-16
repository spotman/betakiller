<?php
namespace BetaKiller\Api\Method\HitPage;

use BetaKiller\Api\Method\AbstractEntityUpdateApiMethod;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\HitPageRedirectRepository;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

final class UpdateApiMethod extends AbstractEntityUpdateApiMethod
{
    private const ARG_TARGET_URL = 'targetUrl';

    /**
     * @var \BetaKiller\Repository\HitPageRedirectRepository
     */
    private HitPageRedirectRepository $targetRepo;

    /**
     * @var \BetaKiller\Helper\UrlHelperInterface
     */
    private UrlHelperInterface $urlHelper;

    /**
     * UpdateApiMethod constructor.
     *
     * @param \BetaKiller\Repository\HitPageRedirectRepository $targetRepo
     * @param \BetaKiller\Factory\UrlHelperFactory             $urlHelperFactory
     */
    public function __construct(
        HitPageRedirectRepository $targetRepo,
        UrlHelperFactory $urlHelperFactory
    ) {
        $this->targetRepo = $targetRepo;
        $this->urlHelper  = $urlHelperFactory->create();
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->identity()
            ->string(self::ARG_TARGET_URL)->optional();
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     * @param \Spotman\Defence\ArgumentsInterface       $arguments
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|mixed|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    protected function processUpdate(
        AbstractEntityInterface $entity,
        ArgumentsInterface $arguments,
        UserInterface $user
    ): ?AbstractEntityInterface {
        $url = $arguments->getString(self::ARG_TARGET_URL);

        if ($url) {
            $targetModel = $this->targetRepo->findByUrl($url);

            if (!$targetModel) {
                $targetModel = $this->targetRepo->create()
                    ->setUrl($url);
            }

            $entity->setRedirect($targetModel);

            $this->saveEntity($entity);

            return $entity;
        }

        return null;
    }
}
