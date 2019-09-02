<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Wamp\Event;

use BetaKiller\Wamp\WampClient;
use Thruway\ClientSession;

class Publish extends AbstractEventTest
{
    protected function doEventTest(WampClient $client, ClientSession $session, string $topicName): void
    {
        $eventArgs = [
            'firstArg'  => 'one',
            'secondArg' => 'two',
        ];

        $promise = $session->publish($topicName, null, $eventArgs, [
            'acknowledge' => true,
            'exclude_me'  => true,
        ]);

        $promise->then(function () use ($eventArgs, $topicName) {
            $this->logger->notice('Successful published to ":name" with :data', [
                ':name' => $topicName,
                ':data' => \json_encode($eventArgs),
            ]);
        });

        $promise->otherwise(function () {
            $this->logger->warning('Publish failed');
        });

        $promise->always(static function () use ($client, $session) {
            $session->shutdown();
            $client->getLoop()->stop();
        });
    }
}
