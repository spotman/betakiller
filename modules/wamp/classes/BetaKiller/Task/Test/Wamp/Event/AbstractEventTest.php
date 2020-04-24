<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Wamp\Event;

use BetaKiller\Task\AbstractTask;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampClientBuilder;
use Psr\Log\LoggerInterface;
use Thruway\ClientSession;

abstract class AbstractEventTest extends AbstractTask
{
    /**
     * @var \BetaKiller\Wamp\WampClientBuilder
     */
    private $clientBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Events constructor.
     *
     * @param \BetaKiller\Wamp\WampClientBuilder $clientFactory
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(
        WampClientBuilder $clientFactory,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->clientBuilder = $clientFactory;
        $this->logger        = $logger;
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
//            'public' => false,
            'topic' => null,
        ];
    }

    public function run(): void
    {
        $topicName = $this->getOption('topic', false) ?: 'test.event';
//        $isPublic  = $this->getOption('public', false) !== false;

//        if ($isPublic) {
        $this->clientBuilder->publicRealm();
//        } else {
//            $this->clientBuilder->internalRealm();
//        }

        $client = $this->clientBuilder->internalAuth()->internalConnection()->create();

        $client->on('open', function (ClientSession $session) use ($client, $topicName) {
            $this->logger->info('WAMP session :id opened in realm ":realm"', [
                ':id'    => $session->getSessionId(),
                ':realm' => $session->getRealm(),
            ]);
            $this->logger->debug('Arguments are '.\json_encode(\func_get_args()));

            $this->doEventTest($client, $session, $topicName);
        });

        // Start and wait for session.close event
        $client->start();
    }

    abstract protected function doEventTest(WampClient $client, ClientSession $session, string $topicName): void;
}
