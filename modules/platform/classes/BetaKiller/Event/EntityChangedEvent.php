<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\IdentityConverterInterface;
use BetaKiller\MessageBus\OutboundEventMessageInterface;
use BetaKiller\Model\AbstractEntityInterface;

class EntityChangedEvent implements OutboundEventMessageInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $id;

    /**
     * EntityChangedEvent constructor.
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     * @param \BetaKiller\IdentityConverterInterface    $converter
     */
    public function __construct(AbstractEntityInterface $entity, IdentityConverterInterface $converter)
    {
        $this->name = $entity::getModelName();
        $this->id   = $converter->encode($entity);
    }

    /**
     * Must return true if message requires at least one handler to be processed
     *
     * @return bool
     */
    public function handlersRequired(): bool
    {
        return false;
    }

    public function getExternalName(): string
    {
        return sprintf('entity.changed.%s.%s', \mb_strtolower($this->name), $this->id);
    }

    /**
     * @return array|null
     */
    public function getExternalData(): ?array
    {
        return null;
    }
}
