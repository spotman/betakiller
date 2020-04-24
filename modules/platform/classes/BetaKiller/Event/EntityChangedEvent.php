<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\IdentityConverterInterface;
use BetaKiller\MessageBus\OutboundEventMessageInterface;
use BetaKiller\Model\AbstractEntityInterface;

final class EntityChangedEvent implements OutboundEventMessageInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $id;

    /**
     * @var int
     */
    private int $ts;

    public static function fromEntity(AbstractEntityInterface $entity, IdentityConverterInterface $converter): self
    {
        return new self($entity::getModelName(), $converter->encode($entity));
    }

    /**
     * EntityChangedEvent constructor.
     *
     * @param string $name
     * @param string $id
     */
    public function __construct(string $name, string $id)
    {
        $this->name = $name;
        $this->id   = $id;
        $this->ts   = \time();
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

    public function getOutboundName(): string
    {
        return sprintf('entity.changed.%s.%s', \mb_strtolower($this->name), $this->id);
    }

    /**
     * @return array|null
     */
    public function getOutboundData(): ?array
    {
        return [
            'ts' => $this->ts,
        ];
    }
}
