<?php
declare(strict_types=1);

namespace BetaKiller\Task\Security;

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

        parent::__construct();
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        echo $this->encrypt->generateKey().\PHP_EOL;
    }
}
