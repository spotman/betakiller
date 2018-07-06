<?php
namespace BetaKiller\Factory;

use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\WebHookModelInterface;
use BetaKiller\WebHook\WebHookInterface;

class WebHookFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * WebHookFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->cacheInstances()
            ->setClassNamespaces('WebHook')
            ->setExpectedInterface(WebHookInterface::class);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \BetaKiller\WebHook\WebHookInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromUrlElement(UrlElementInterface $model): WebHookInterface
    {
        if (!$model instanceof WebHookModelInterface) {
            throw new FactoryException('Can not create WebHook from URL element :codename of type :class', [
                ':codename' => $model->getCodename(),
                ':class'    => \get_class($model),
            ]);
        }

        $codename = $model->getCodename();

        /** @var \BetaKiller\WebHook\WebHookInterface $instance */
        $instance = $this->factory->create($codename);

        return $instance->setModel($model);
    }
}
