<?php

declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Factory\RepositoryFactoryInterface;
use BetaKiller\IdentityConverterInterface;

final class EncodeId extends AbstractTask
{
    private const ARG_ENTITY = 'entity';
    private const ARG_ID     = 'id';

    /**
     * @var \BetaKiller\IdentityConverterInterface
     */
    private IdentityConverterInterface $identityConverter;

    /**
     * @var \BetaKiller\Factory\RepositoryFactoryInterface
     */
    private RepositoryFactoryInterface $repoFactory;

    /**
     * DecodeId constructor.
     *
     * @param \BetaKiller\Factory\RepositoryFactoryInterface $repoFactory
     * @param \BetaKiller\IdentityConverterInterface         $identityConverter
     */
    public function __construct(RepositoryFactoryInterface $repoFactory, IdentityConverterInterface $identityConverter)
    {
        $this->repoFactory       = $repoFactory;
        $this->identityConverter = $identityConverter;
    }

    /**
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
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
        $entityName = $params->getString(self::ARG_ENTITY);
        $id         = $params->getString(self::ARG_ID);

        $entity = $this->repoFactory->create($entityName)->getById($id);

        echo 'Encoded value is: '.$this->identityConverter->encode($entity).PHP_EOL;
    }
}
