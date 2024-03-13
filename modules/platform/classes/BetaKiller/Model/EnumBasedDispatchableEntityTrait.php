<?php
namespace BetaKiller\Model;

use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Url\UrlPrototype;
use BetaKiller\Url\UrlPrototypeException;
use LogicException;

trait EnumBasedDispatchableEntityTrait
{
    use EnumBasedEntityTrait;

    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    final public static function getUrlContainerKey(): string
    {
        return static::getModelName();
    }

    /**
     * Entity may return instances of linked entities (if exists).
     * This method is used to fetch missing entities in UrlContainer walking through links between them
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getLinkedEntities(): array
    {
        // No entities by default
        return [];
    }

    /**
     * Returns null for default action name (read)
     *
     * @return string|null
     */
    public function getUrlParameterAccessAction(): ?string
    {
        // Use default one
        return null;
    }

    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getUrlKeyValue(string $key): string
    {
        if ($key !== UrlPrototype::KEY_ID) {
            throw new UrlPrototypeException('Enum-based url parameter [:name] cannot be fetched by ":key" key', [
                ':name' => $this->getCodename(),
                ':key'  => $key,
            ]);
        }

        return $this->getID();
    }

    /**
     * Returns true if current parameter is the same as provided one
     *
     * @param \BetaKiller\Model\ConfigBasedDispatchableEntityInterface|mixed $parameter
     *
     * @return bool
     */
    public function isSameAs(UrlParameterInterface $parameter): bool
    {
        if (!$parameter instanceof static) {
            throw new LogicException();
        }

        // Enums allow that
        return $this === $parameter;
    }


    public function isCachingAllowed(): bool
    {
        // Allow caching for config-based Entities
        return true;
    }
}
