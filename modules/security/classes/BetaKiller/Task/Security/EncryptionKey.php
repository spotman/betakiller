<?php

declare(strict_types=1);

namespace BetaKiller\Task\Security;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Security\EncryptionInterface;
use BetaKiller\Task\AbstractTask;

class EncryptionKey extends AbstractTask
{
    /**
     * @var \BetaKiller\Security\EncryptionInterface
     */
    private EncryptionInterface $encrypt;

    /**
     * EncryptionKey constructor.
     *
     * @param \BetaKiller\Security\EncryptionInterface $encrypt
     */
    public function __construct(EncryptionInterface $encrypt)
    {
        $this->encrypt = $encrypt;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @return array
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        echo $this->encrypt->generateKey().\PHP_EOL;
    }
}
