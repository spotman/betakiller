<?php
namespace BetaKiller\Url;

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
     * @throws \BetaKiller\Url\UrlElementException
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

        // No params check for static URls
        if (!$model->hasDynamicUrl()) {
            return true;
        }

        // No optional params => check passed
        if (!$params || $params->countParameters() === 0) {
            return true;
        }

        $key = UrlPrototype::fromString($model->getUri())->getDataSourceName();

        // If no bound UrlParameter in current set => check failed
        if (!$this->parameters->hasParameter($key)) {
            return false;
        }

        $elementParam = $params->getParameter($key);
        $currentParam = $this->parameters->getParameter($key);

        return $currentParam->isSameAs($elementParam);
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
     * Get all UrlElements
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getItems(): array
    {
        return array_values($this->items);
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
