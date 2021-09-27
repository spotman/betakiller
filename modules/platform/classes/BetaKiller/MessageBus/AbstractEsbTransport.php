<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelper;
use Clue\React\Redis\Client;
use Clue\React\Redis\Factory;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\reject;

abstract class AbstractEsbTransport implements EventTransportInterface
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \BetaKiller\MessageBus\EventSerializerInterface
     */
    protected EventSerializerInterface $serializer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var \Clue\React\Redis\Client|null
     */
    private ?Client $pubClient = null;

    /**
     * @var \Clue\React\Redis\Client|null
     */
    private ?Client $subClient = null;

    /**
     * @var bool
     */
    private bool $isConsuming = false;

    /**
     * @var callable[]
     */
    private array $singleHandlers = [];

    /**
     * @var callable[]
     */
    private array $patternHandlers = [];

    /**
     * AbstractEsbTransport constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface              $appEnv
     * @param \BetaKiller\MessageBus\EventSerializerInterface $serializer
     * @param \Psr\Log\LoggerInterface                        $logger
     */
    public function __construct(AppEnvInterface $appEnv, EventSerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->appEnv     = $appEnv;
        $this->serializer = $serializer;
        $this->logger     = $logger;
    }

    /**
     * @inheritDoc
     */
    public function startConsuming(LoopInterface $loop): void
    {
        if ($this->isConsuming) {
            throw new \LogicException('Consuming is already started');
        }

        $client = $this->getSubClient($loop);

        // Start consuming
        foreach ($this->singleHandlers as $channel => $handler) {
            $client->subscribe($channel);
        }

        foreach ($this->patternHandlers as $pattern => $handler) {
            $client->psubscribe($pattern);
        }

        $this->isConsuming = true;
    }

    /**
     * @inheritDoc
     */
    public function stopConsuming(LoopInterface $loop): void
    {
        if ($this->subClient) {
            // Unsubscribe from everything
            foreach ($this->singleHandlers as $channel => $handler) {
                $this->subClient->unsubscribe($channel);
            }

            foreach ($this->patternHandlers as $pattern => $handler) {
                $this->subClient->punsubscribe($pattern);
            }

            // A graceful stop
            $this->subClient->end();
        }

        $this->isConsuming = false;
    }

    protected function processSingleMessage(string $channel, string $payload): PromiseInterface
    {
        $handler = $this->singleHandlers[$channel] ?? null;

        if (!$handler) {
            throw new \LogicException(sprintf('Missing handler for channel "%s"', $channel));
        }

        $event = $this->serializer->decode($payload);

        return $this->processEvent($event, $handler);
    }

    protected function processPatternMessage(string $pattern, string $payload): PromiseInterface
    {
        $handler = $this->patternHandlers[$pattern] ?? null;

        if (!$handler) {
            throw new \LogicException(sprintf('Missing handler for pattern "%s"', $pattern));
        }

        $event = $this->serializer->decode($payload);

        return $this->processEvent($event, $handler);
    }

    protected function processEvent(EventMessageInterface $event, callable $handler): PromiseInterface
    {
        try {
            $result = $handler($event);

            if (!$result instanceof PromiseInterface) {
                throw new \LogicException(sprintf('Handler must return a Promise for event "%s"', get_class($event)));
            }

            return $result;
        } catch (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);

            return reject();
        }
    }

    abstract protected function getTopicName(): string;

    private function getPubClient(LoopInterface $loop): Client
    {
        if (!$this->pubClient) {
            $this->pubClient = $this->createClient($loop);
        }

        return $this->pubClient;
    }

    private function getSubClient(LoopInterface $loop): Client
    {
        if (!$this->subClient) {
            $this->subClient = $this->createClient($loop);

            // Add event handlers only once
            $this->subClient->on('message', function ($channel, $payload) {
                try {
                    $this->processSingleMessage($channel, $payload);
                } catch (\Throwable $e) {
                    LoggerHelper::logRawException($this->logger, $e);
                }
            });

            $this->subClient->on('pmessage', function ($pattern, $channel, $payload) {
                try {
                    $this->processPatternMessage($pattern, $payload);
                } catch (\Throwable $e) {
                    LoggerHelper::logRawException($this->logger, $e);
                }
            });
        }

        return $this->subClient;
    }

    private function createClient(LoopInterface $loop): Client
    {
        $host = $this->appEnv->getEnvVariable('REDIS_HOST');
        $port = $this->appEnv->getEnvVariable('REDIS_PORT');

        $uri = sprintf('redis://%s:%d?timeout=5', $host, $port);

        $factory = new Factory($loop);

        $client = $factory->createLazyClient($uri);

        $client->on('error', function (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        });

        return $client;
    }

    protected function publishEvent(ExternalEventMessageInterface $event): void
    {
        $loop = \React\EventLoop\Factory::create();

        $client = $this->createClient($loop);

        $channel = $this->makeChannelName($event::getExternalName());
        $message = $this->serializer->encode($event);

        // Send and close connection
        $client->publish($channel, $message)->otherwise(function (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        });

        $client->end();

        $loop->run();
    }

    protected function subscribeSingle(string $eventName, callable $handler): void
    {
        $channel = $this->makeChannelName($eventName);

        if (isset($this->singleHandlers[$channel])) {
            throw new \LogicException(sprintf('Already subscribed to single event "%s"', $channel));
        }

        $this->singleHandlers[$channel] = $handler;
    }

    protected function subscribePattern(string $eventPattern, callable $handler): void
    {
        $channel = $this->makeChannelName($eventPattern);

        if (isset($this->patternHandlers[$channel])) {
            throw new \LogicException(sprintf('Already subscribed to event pattern "%s"', $channel));
        }

        $this->patternHandlers[$channel] = $handler;
    }

    private function makeChannelName(string $eventName): string
    {
        // Prefix with app name and environment
        return implode('.', [
            $this->appEnv->getAppCodename(),
            $this->appEnv->getModeName(),
            $this->getTopicName(),
            $eventName,
        ]);
    }
}
