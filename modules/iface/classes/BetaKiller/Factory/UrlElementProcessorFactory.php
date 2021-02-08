<?php
namespace BetaKiller\Factory;

use BetaKiller\Url\ActionModelInterface;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\ElementProcessor\UrlElementProcessorInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementInterface;

/**
 * Factory of URL element processor like IFace, WabHok and etc
 */
class UrlElementProcessorFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->cacheInstances()
            ->setClassNamespaces('Url', 'ElementProcessor')
            ->setClassSuffix('UrlElementProcessor')
            ->setExpectedInterface(UrlElementProcessorInterface::class);
    }

    /**
     * Creating URL element processor
     *
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \BetaKiller\Url\ElementProcessor\UrlElementProcessorInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromUrlElement(UrlElementInterface $model): UrlElementProcessorInterface
    {
        switch (true) {
            case $model instanceof IFaceModelInterface:
                $className = 'IFace';
                break;

            case $model instanceof DummyModelInterface:
                $className = 'Dummy';
                break;

            case $model instanceof ActionModelInterface:
                $className = 'Action';
                break;

            default:
                throw new UrlElementException('Unknown IFace Url element type ":type" with codename ":codename"', [
                    ':type'     => \get_class($model),
                    ':codename' => $model->getCodename(),
                ]);
        }

        return $this->factory->create($className);
    }
}
