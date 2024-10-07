<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Wamp\Event;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampClientBuilder;
use Psr\Log\LoggerInterface;
use Thruway\ClientSession;

abstract class AbstractEventTest extends AbstractTask
{
    private const ARG_TOPIC = 'topic';

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
        $this->clientBuilder = $clientFactory;
        $this->logger        = $logger;
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
        return [
            $builder->string(self::ARG_TOPIC)->optional('test.event')->label('Topic codename'),
//            'public' => false,
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $topicName = $params->getString(self::ARG_TOPIC);
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
