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
    private const STATUSES_VALID = ['ENABLED'];

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
        $hooks     = $this->mangoPayApi->Hooks->GetAll();
        $errorsQty = 0;
        foreach ($hooks as $hook) {
            $this->logger->debug(
                'Check webhook: :eventType', [
                    ':eventType' => $hook->EventType,
                ]
            );

            /**
             * @var \MangoPay\Hook $hook
             */
            try {
                if (!\in_array($hook->Status, self::STATUSES_VALID, true)) {
                    throw new WebHookException(
                        'Invalid status: :status. Event type: :eventType', [
                            ':status'    => $hook->Status,
                            ':eventType' => $hook->EventType,
                        ]
                    );
                }
            } catch (\Exception $exception) {
                $errorsQty++;
                $this->logException($this->logger, $exception);
            }
        }

        $this->logger->info(
            'Checking completed. Errors: :errorsQty', [
                ':errorsQty' => $errorsQty,
            ]
        );
    }
}
