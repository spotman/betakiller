<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Url\ActionModelInterface;
use BetaKiller\Url\DummyInstance;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInstanceInterface;
use BetaKiller\Url\UrlElementInterface;

class UrlElementInstanceFactory
{
    /**
     * @var \BetaKiller\Factory\IFaceFactory
     */
    private $ifaceFactory;

    /**
     * @var \BetaKiller\Factory\ActionFactory
     */
    private $actionFactory;

    /**
     * UrlElementInstanceFactory constructor.
     *
     * @param \BetaKiller\Factory\IFaceFactory  $ifaceFactory
     * @param \BetaKiller\Factory\ActionFactory $actionFactory
     */
    public function __construct(IFaceFactory $ifaceFactory, ActionFactory $actionFactory)
    {
        $this->ifaceFactory = $ifaceFactory;
        $this->actionFactory = $actionFactory;
    }

    public function createFromUrlElement(UrlElementInterface $element): UrlElementInstanceInterface
    {
        if ($element instanceof IFaceModelInterface) {
            return $this->ifaceFactory->createFromUrlElement($element);
        }

        if ($element instanceof ActionModelInterface) {
            return $this->actionFactory->createFromUrlElement($element);
        }

        if ($element instanceof DummyModelInterface) {
            return new DummyInstance($element);
        }

        throw new FactoryException('Unknown UrlElement ":name" type', [
            ':name' => $element->getCodename(),
        ]);
    }
}
