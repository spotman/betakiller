<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\Exception\IFaceStackException;
use BetaKiller\IFace\Url\UrlParametersInterface;

class IFaceStack
{
    /**
     * @var \BetaKiller\IFace\IFaceInterface
     */
    private $current;

    /**
     * @var \BetaKiller\IFace\IFaceInterface[]
     */
    private $items;

    /**
     * @var UrlParametersInterface
     */
    private $parameters;

    /**
     * IFaceStack constructor.
     *
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $parameters
     */
    public function __construct(UrlParametersInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    public function push(IFaceInterface $iface)
    {
        if ($this->has($iface)) {
            throw new IFaceStackException('Duplicate insert for :codename', [':codename' => $iface->getCodename()]);
        }

        $codename               = $iface->getCodename();
        $this->items[$codename] = $iface;
        $this->current          = $iface;
    }

    public function has(IFaceInterface $iface)
    {
        return isset($this->items[$iface->getCodename()]);
    }

    /**
     * Return codenames of pushed IFaces
     *
     * @return string[]
     */
    public function getCodenames()
    {
        return array_keys($this->items);
    }

    /**
     * @deprecated IFace stack must be persistent
     */
    public function clear()
    {
        $this->items   = [];
        $this->current = null;
    }

    public function getCurrent()
    {
        return $this->current;
    }

    public function isCurrent(IFaceInterface $iface, UrlParametersInterface $parameters = null)
    {
        if (!$this->current || $this->current->getCodename() !== $iface->getCodename()) {
            return false;
        }

        if (!$parameters) {
            return true;
        }

        foreach ($parameters->getAllEntities() as $key => $paramModel) {
            if (!$this->parameters->hasEntity($key)) {
                return false;
            }

            $currentModel = $this->parameters->getEntity($key);

            if ($paramModel->getID() !== $currentModel->getID()) {
                return false;
            }
        }

        return true;
    }
}
