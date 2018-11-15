<?php
namespace BetaKiller\Factory;

use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Model\WebHookModelInterface;
use BetaKiller\Url\ActionModelInterface;
use BetaKiller\Url\ElementProcessor\UrlElementProcessorInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;

/**
 * Factory of URL element processor like IFace, WabHok and etc
 */
class UrlElementProcessorFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
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

            case $model instanceof WebHookModelInterface:
                $className = 'WebHook';
                break;

            case $model instanceof ActionModelInterface:
                $className = 'Action';
                break;

            default:
                throw new UrlElementException('Unknown IFace Url element type :codename', [
                    ':codename' => $model->getCodename(),
                ]);
        }

        return $this->factory->create($className);
    }
}
