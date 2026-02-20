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
    private ?string $entityName = null;

    /**
     * @var string|null
     */
    private ?string $entityAction = null;

    /**
     * @inheritDoc
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            self::OPTION_ENTITY_NAME   => $this->getEntityModelName(),
            self::OPTION_ENTITY_ACTION => $this->getEntityActionName(),
        ]);
    }

    /**
     * @inheritDoc
     */
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
     * @inheritDoc
     */
    public function getEntityModelName(): ?string
    {
        return $this->entityName;
    }

    /**
     * @inheritDoc
     */
    public function getEntityActionName(): ?string
    {
        return $this->entityAction;
    }
}
