<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Url\UrlElementInterface;

class UrlBehaviourFactory
{
    public const BEHAVIOUR_SINGLE   = 'Single';
    public const BEHAVIOUR_MULTIPLE = 'Multiple';
    public const BEHAVIOUR_TREE     = 'Tree';

    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * @var \BetaKiller\Factory\UrlHelperFactory
     */
    private $urlHelperFactory;

    /**
     * UrlBehaviourFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder, UrlHelperFactory $urlHelperFactory)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setExpectedInterface(UrlBehaviourInterface::class)
            ->setClassNamespaces(...UrlBehaviourInterface::CLASS_NS)
            ->setClassSuffix(UrlBehaviourInterface::CLASS_SUFFIX)
            ->cacheInstances();

        $this->urlHelperFactory = $urlHelperFactory;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \BetaKiller\Url\Behaviour\UrlBehaviourInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function fromUrlElement(UrlElementInterface $model): UrlBehaviourInterface
    {
        $codename = $this->detectCodename($model);

        return $this->create($codename);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return string
     */
    private function detectCodename(UrlElementInterface $model): string
    {
        if ($model->hasTreeBehaviour()) {
            return self::BEHAVIOUR_TREE;
        }

        if ($model->hasDynamicUrl()) {
            return self::BEHAVIOUR_MULTIPLE;
        }

        // Raw mapping by default
        return self::BEHAVIOUR_SINGLE;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Url\Behaviour\UrlBehaviourInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename): UrlBehaviourInterface
    {
        return $this->factory->create($codename, [
            'urlHelper' => $this->urlHelperFactory->create(),
        ]);
    }
}
