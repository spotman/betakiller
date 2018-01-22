<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\IFace\IFaceModelInterface;

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
     * UrlBehaviourFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
            ->setExpectedInterface(UrlBehaviourInterface::class)
            ->setClassNamespaces(...UrlBehaviourInterface::CLASS_NS)
            ->setClassSuffix(UrlBehaviourInterface::CLASS_SUFFIX)
            ->cacheInstances();
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\Url\Behaviour\UrlBehaviourInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function fromIFaceModel(IFaceModelInterface $model): UrlBehaviourInterface
    {
        $codename = $this->detectCodename($model);

        return $this->create($codename);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return string
     */
    private function detectCodename(IFaceModelInterface $model): string
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
        return $this->factory->create($codename);
    }
}
