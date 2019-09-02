<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Wamp\Event;

use BetaKiller\Wamp\WampClient;
use Thruway\ClientSession;

class Subscribe extends AbstractEventTest
{
    protected function doEventTest(WampClient $client, ClientSession $session, string $topicName): void
    {
        // Subscribe first
        $promise = $session->subscribe($topicName, function () use ($client, $session) {
            $this->logger->notice('Message received :data', [
                ':data' => \json_encode(\func_get_args()),
            ]);

//            $session->shutdown();
//            $client->getLoop()->stop();
        });

        $promise->then(function () use ($topicName) {
            $this->logger->notice('Successfully subscribed to topic ":name" :result', [
                ':name'   => $topicName,
                ':result' => json_encode(\func_get_args()),
            ]);
        });

        $promise->otherwise(function () use ($client, $session) {
            $this->logger->warning('Subscribe failed');
            $session->shutdown();
            $client->getLoop()->stop();
        });
    }
}
