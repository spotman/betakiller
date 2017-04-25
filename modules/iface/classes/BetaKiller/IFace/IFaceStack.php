<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\Exception\IFaceStackException;
use BetaKiller\IFace\Url\UrlParameters;
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
     * @var UrlParameters
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

        $this->items[$iface->getCodename()] = $iface;
        $this->current = $iface;
    }

    public function has(IFaceInterface $iface)
    {
        return isset($this->items[$iface->getCodename()]);
    }

    public function clear()
    {
        $this->items = [];
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

        $currentParams = $this->parameters;

        foreach ($parameters->getAll() as $key => $paramModel) {
            /** @var \BetaKiller\IFace\Url\UrlDataSourceInterface $paramModel */

            if (!$currentParams->has($key)) {
                return false;
            }

            /** @var \BetaKiller\IFace\Url\UrlDataSourceInterface $currentModel */
            $currentModel = $currentParams->get($key);

            if ($paramModel->getUrlItemID() !== $currentModel->getUrlItemID()) {
                return false;
            }
        }

        return true;
    }
}
