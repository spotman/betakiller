<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Factory\RepositoryFactory;
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
     * @var \BetaKiller\Factory\RepositoryFactory
     */
    private RepositoryFactory $repoFactory;

    /**
     * DecodeId constructor.
     *
     * @param \BetaKiller\Factory\RepositoryFactory  $repoFactory
     * @param \BetaKiller\IdentityConverterInterface $identityConverter
     */
    public function __construct(RepositoryFactory $repoFactory, IdentityConverterInterface $identityConverter)
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
