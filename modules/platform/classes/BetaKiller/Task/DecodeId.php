<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\IdentityConverterInterface;

final class DecodeId extends AbstractTask
{
    private const ARG_ENTITY = 'entity';
    private const ARG_ID = 'id';

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
        parent::__construct();

        $this->identityConverter = $identityConverter;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(): array
    {
        return [
            self::ARG_ENTITY => null,
            self::ARG_ID => null,
        ];
    }

    public function run(): void
    {
        $entity = $this->getOption(self::ARG_ENTITY, true);
        $id = $this->getOption(self::ARG_ID, true);

        echo 'Decoded value is: '.$this->identityConverter->decode($entity, $id).PHP_EOL;
    }
}
