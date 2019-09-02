<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\IdentityConverterInterface;
use BetaKiller\MessageBus\OutboundEventMessageInterface;
use BetaKiller\Model\AbstractEntityInterface;

class EntityChanged implements OutboundEventMessageInterface
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
     * EntityChanged constructor.
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
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        // No data
        return [];
    }
}
