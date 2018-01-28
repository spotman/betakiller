<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\Exception\IFaceStackException;
use BetaKiller\Url\UrlContainerInterface;

class IFaceModelsStack implements \IteratorAggregate
{
    /**
     * @var \BetaKiller\IFace\IFaceModelInterface
     */
    private $current;

    /**
     * @var \BetaKiller\IFace\IFaceModelInterface[]
     */
    private $items;

    /**
     * @var UrlContainerInterface
     */
    private $parameters;

    /**
     * IFaceModelsStack constructor.
     *
     * @param \BetaKiller\Url\UrlContainerInterface $parameters
     */
    public function __construct(UrlContainerInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @throws \BetaKiller\IFace\Exception\IFaceStackException
     */
    public function push(IFaceModelInterface $model): void
    {
        if ($this->has($model)) {
            throw new IFaceStackException('Duplicate insert for :codename', [':codename' => $model->getCodename()]);
        }

        $codename               = $model->getCodename();
        $this->items[$codename] = $model;
        $this->current          = $model;
    }

    public function has(IFaceModelInterface $model): bool
    {
        return isset($this->items[$model->getCodename()]);
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
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @deprecated IFace stack must be persistent
     */
    public function clear(): void
    {
        $this->items   = [];
        $this->current = null;
    }

    /**
     * @return \BetaKiller\IFace\IFaceModelInterface|null
     */
    public function getCurrent(): ?IFaceModelInterface
    {
        return $this->current;
    }

    public function isCurrent(IFaceModelInterface $model, ?UrlContainerInterface $parameters = null): bool
    {
        if (!$this->current || $this->current->getCodename() !== $model->getCodename()) {
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
