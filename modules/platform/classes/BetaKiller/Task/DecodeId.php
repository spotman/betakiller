<?php

declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\IdentityConverterInterface;

final class DecodeId extends AbstractTask
{
    private const ARG_ENTITY = 'entity';
    private const ARG_ID     = 'id';

    /**
     * @var \BetaKiller\IdentityConverterInterface
     */
    private IdentityConverterInterface $identityConverter;

    /**
     * DecodeId constructor.
     *
     * @param \BetaKiller\IdentityConverterInterface $identityConverter
     */
    public function __construct(IdentityConverterInterface $identityConverter)
    {
        $this->identityConverter = $identityConverter;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->string(self::ARG_ENTITY)->required()->label('Entity codename'),
            $builder->string(self::ARG_ID)->required()->label('Entity ID'),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $entity = $params->getString(self::ARG_ENTITY);
        $id     = $params->getString(self::ARG_ID);

        echo 'Decoded value is: '.$this->identityConverter->decode($entity, $id).PHP_EOL;
    }
}
