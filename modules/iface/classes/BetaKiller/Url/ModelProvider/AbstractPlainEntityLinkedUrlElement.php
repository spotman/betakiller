<?php
declare(strict_types=1);

namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Url\EntityLinkedUrlElementInterface;

abstract class AbstractPlainEntityLinkedUrlElement extends AbstractPlainUrlElementModel implements
    EntityLinkedUrlElementInterface
{
    public const OPTION_ENTITY_NAME   = 'entity';
    public const OPTION_ENTITY_ACTION = 'entityAction';

    /**
     * @var string|null
     */
    private $entityName;

    /**
     * @var string|null
     */
    private $entityAction;

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            self::OPTION_ENTITY_NAME   => $this->getEntityModelName(),
            self::OPTION_ENTITY_ACTION => $this->getEntityActionName(),
        ]);
    }

    public function fromArray(array $data): void
    {
        if (isset($data[self::OPTION_ENTITY_NAME])) {
            $this->entityName = (string)$data[self::OPTION_ENTITY_NAME];
        }

        if (isset($data[self::OPTION_ENTITY_ACTION])) {
            $this->entityAction = (string)$data[self::OPTION_ENTITY_ACTION];
        }

        parent::fromArray($data);
    }

    /**
     * Returns model name of the linked entity
     *
     * @return string
     */
    public function getEntityModelName(): ?string
    {
        return $this->entityName;
    }

    /**
     * Returns entity [primary] action, applied by this IFace
     *
     * @return string
     */
    public function getEntityActionName(): ?string
    {
        return $this->entityAction;
    }
}
