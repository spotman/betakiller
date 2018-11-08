<?php
declare(strict_types=1);

namespace BetaKiller\Task\Security;

use BetaKiller\Security\Encryption;
use BetaKiller\Task\AbstractTask;

class EncryptionKey extends AbstractTask
{
    /**
     * @var \BetaKiller\Security\Encryption
     */
    private $encrypt;

    /**
     * EncryptionKey constructor.
     *
     * @param \BetaKiller\Security\Encryption $encrypt
     */
    public function __construct(Encryption $encrypt)
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
