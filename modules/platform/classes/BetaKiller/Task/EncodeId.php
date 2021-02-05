<?php
declare(strict_types=1);

namespace BetaKiller\Task;

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
        parent::__construct();

        $this->repoFactory       = $repoFactory;
        $this->identityConverter = $identityConverter;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(): array
    {
        return [
            self::ARG_ENTITY => null,
            self::ARG_ID     => null,
        ];
    }

    public function run(): void
    {
        $entityName = $this->getOption(self::ARG_ENTITY, true);
        $id         = $this->getOption(self::ARG_ID, true);

        $entity = $this->repoFactory->create($entityName)->getById($id);

        echo 'Encoded value is: '.$this->identityConverter->encode($entity).PHP_EOL;
    }
}
