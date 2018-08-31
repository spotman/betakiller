<?php
declare(strict_types=1);

namespace BetaKiller\Task\Transactions;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\WebHook\WebHookException;
use MangoPay\MangoPayApi;

class CheckMangoPayHooks extends AbstractTask
{
    private const HOOK_STATUS_VALID = 'ENABLED';

    use LoggerHelperTrait;

    /**
     * @var \MangoPay\MangoPayApi
     */
    private $mangoPayApi;

    /**
     * @var \BetaKiller\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \MangoPay\MangoPayApi           $mangoPayApi
     * @param \BetaKiller\Log\LoggerInterface $logger
     */
    public function __construct(
        MangoPayApi $mangoPayApi,
        LoggerInterface $logger
    ) {
        $this->mangoPayApi = $mangoPayApi;
        $this->logger      = $logger;

        parent::__construct();
    }

    public function run(): void
    {
        $hooks = $this->mangoPayApi->Hooks->GetAll();
        foreach ($hooks as $hook) {
            /**
             * @var \MangoPay\Hook $hook
             */
            try {
                if ($hook->Status !== self::HOOK_STATUS_VALID) {
                    throw new WebHookException(
                        'Invalid status ":status", event type ":eventType"', [
                            ':status'    => $hook->Status,
                            ':eventType' => $hook->EventType,
                        ]
                    );
                }
            } catch (\Exception $exception) {
                $this->logException($this->logger, $exception);
            }
        }
    }
}
