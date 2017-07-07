<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\Exception\IFaceStackException;
use BetaKiller\IFace\Url\UrlContainerInterface;

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
     * @var UrlContainerInterface
     */
    private $parameters;

    /**
     * IFaceStack constructor.
     *
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $parameters
     */
    public function __construct(UrlContainerInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    public function push(IFaceInterface $iface): void
    {
        if ($this->has($iface)) {
            throw new IFaceStackException('Duplicate insert for :codename', [':codename' => $iface->getCodename()]);
        }

        $codename               = $iface->getCodename();
        $this->items[$codename] = $iface;
        $this->current          = $iface;
    }

    public function has(IFaceInterface $iface): bool
    {
        return isset($this->items[$iface->getCodename()]);
    }

    /**
     * Return codenames of pushed IFaces
     *
     * @return string[]
     */
    public function getCodenames(): array
    {
        return array_keys($this->items);
    }

    /**
     * @deprecated IFace stack must be persistent
     */
    public function clear(): void
    {
        $this->items   = [];
        $this->current = null;
    }

    public function getCurrent(): ?IFaceInterface
    {
        return $this->current;
    }

    public function isCurrent(IFaceInterface $iface, UrlContainerInterface $parameters = null): bool
    {
        if (!$this->current || $this->current->getCodename() !== $iface->getCodename()) {
            return false;
        }

        if (!$parameters) {
            return true;
        }

        foreach ($parameters->getAllParameters() as $key => $providedParam) {
            if (!$this->parameters->hasParameter($key)) {
                return false;
            }

            $currentParam = $this->parameters->getParameter($key);

            if (!$currentParam->isSameAs($providedParam)) {
                return false;
            }
        }

        return true;
    }
}
