<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Config\EmailConfigInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\MessageBus\EventBusConfigInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use Minion_CLI;
use Psr\Log\LoggerInterface;

class CheckConfig extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\EmailConfigInterface
     */
    private EmailConfigInterface $emailConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\MessageBus\EventBusConfigInterface
     */
    private EventBusConfigInterface $eventBusConfig;

    /**
     * CheckConfig constructor.
     *
     * @param \BetaKiller\Config\EmailConfigInterface        $emailConfig
     * @param \BetaKiller\MessageBus\EventBusConfigInterface $eventBusConfig
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(
        EmailConfigInterface    $emailConfig,
        EventBusConfigInterface $eventBusConfig,
        LoggerInterface         $logger
    ) {
        parent::__construct();

        $this->emailConfig    = $emailConfig;
        $this->eventBusConfig = $eventBusConfig;
        $this->logger         = $logger;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [
            // No options here
        ];
    }

    public function run(): void
    {
        $this->showEmailConfig();

        echo PHP_EOL.PHP_EOL;

        $this->showEventsConfig();
    }

    private function showEmailConfig(): void
    {
        echo '[Email]'.PHP_EOL.PHP_EOL;

        $data = [
            'Host'       => $this->emailConfig->getHost(),
            'Port'       => $this->emailConfig->getPort(),
            'Timeout'    => $this->emailConfig->getTimeout(),
            'Username'   => $this->emailConfig->getUsername(),
            'Password'   => $this->emailConfig->getPassword(),
            'Encryption' => $this->emailConfig->useEncryption(),
            'Domain'     => $this->emailConfig->getDomain(),
            'From.email' => $this->emailConfig->getFromEmail(),
            'From.name'  => $this->emailConfig->getFromName(),
        ];

        foreach ($data as $label => $value) {
            echo sprintf('%s: %s'.PHP_EOL, $label, Minion_CLI::color($value, 'green'));
        }
    }

    private function showEventsConfig(): void
    {
        echo '[Events]'.PHP_EOL.PHP_EOL;

        foreach ($this->eventBusConfig->getEventsMap() as $eventName => $eventHandlers) {
            echo sprintf("%s:".PHP_EOL, Minion_CLI::color($eventName, 'blue'));

            if (!$eventHandlers) {
                echo Minion_CLI::color('  No handlers defined', 'red').PHP_EOL.PHP_EOL;
                continue;
            }

            foreach ($eventHandlers as $handler) {
                echo sprintf('  %s'.PHP_EOL, Minion_CLI::color($handler, 'green'));
            }

            echo PHP_EOL;
        }
    }
}
