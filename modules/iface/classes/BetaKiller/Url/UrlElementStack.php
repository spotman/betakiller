<?php
namespace BetaKiller\Url;

use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Url\Container\UrlContainerInterface;

class UrlElementStack implements \IteratorAggregate
{
    /**
     * @var \BetaKiller\Url\UrlElementInterface
     */
    private $current;

    /**
     * @var \BetaKiller\Url\UrlElementInterface[]
     */
    private $items = [];

    /**
     * @var UrlContainerInterface
     */
    private $parameters;

    /**
     * UrlElementStack constructor.
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $parameters
     */
    public function __construct(UrlContainerInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function push(UrlElementInterface $model): void
    {
        if ($this->has($model)) {
            throw new UrlElementException('Duplicate insert for :codename', [':codename' => $model->getCodename()]);
        }

        $codename               = $model->getCodename();
        $this->items[$codename] = $model;
        $this->current          = $model;
    }

    public function has(UrlElementInterface $model, UrlContainerInterface $params = null): bool
    {
        if (!isset($this->items[$model->getCodename()])) {
            return false;
        }

        // No optional params => check passed
        if (!$params || $params->countParameters() === 0) {
            return true;
        }

        // No current parameters => nothing to check => check passed
        if ($this->parameters->countParameters() === 0) {
            return true;
        }

        // Find parameters intersection
        foreach ($params->getAllParameters() as $key => $providedParam) {
            if (!$this->parameters->hasParameter($key)) {
                continue;
            }

            $currentParam = $this->parameters->getParameter($key);

            if ($currentParam->isSameAs($providedParam)) {
                return true;
            }
        }

        // No params intersection found => check failed
        return false;
    }

    /**
     * Return codenames of pushed URL elements
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
     * @return \Iterator|\BetaKiller\Url\UrlElementInterface[]
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface
     */
    public function getCurrent(): UrlElementInterface
    {
        return $this->current;
    }

    /**
     * @return bool
     */
    public function hasCurrent(): bool
    {
        return (bool)$this->current;
    }

    public function isCurrent(UrlElementInterface $model, ?UrlContainerInterface $parameters = null): bool
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
