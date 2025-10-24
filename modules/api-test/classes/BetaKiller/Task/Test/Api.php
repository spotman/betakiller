<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Api\ApiFacade;
use BetaKiller\Console\ConsoleInput;
use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

use const JSON_PRETTY_PRINT;

class Api extends AbstractTask
{
    private const ARG_TARGET = 'target';
    private const ARG_REQ    = 'req';
    private const ARG_P1     = 'p1';
    private const ARG_P2     = 'p2';
    private const ARG_P3     = 'p3';
    private const ARG_P4     = 'p4';

    /**
     * @var \BetaKiller\Api\ApiFacade
     */
    private $api;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private UserInterface $user;

    /**
     * Api constructor.
     *
     * @param \BetaKiller\Api\ApiFacade       $api
     * @param \Psr\Log\LoggerInterface        $logger
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct(ApiFacade $api, LoggerInterface $logger, UserInterface $user)
    {
        $this->api    = $api;
        $this->logger = $logger;
        $this->user   = $user;
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
            $builder->string(self::ARG_TARGET)->required(),
            $builder->string(self::ARG_REQ),
            $builder->string(self::ARG_P1),
            $builder->string(self::ARG_P2),
            $builder->string(self::ARG_P3),
            $builder->string(self::ARG_P4),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $targetString = $params->getString(self::ARG_TARGET);

        $arguments = $this->getCallArguments($params);

        [$resourceName, $methodName] = explode('.', $targetString, 2);

        $this->logger->debug('Calling ":resource->:method" with arguments ":args"', [
            ':resource' => $resourceName,
            ':method'   => $methodName,
            ':args'     => json_encode($arguments),
        ]);

        $start = \microtime(true);

        \Database_Query::resetQueryCount();
        \Database_Query::enableQueryLog();

        try {
            $response = $this->api->call($resourceName, $methodName, $arguments, $this->user);
        } catch (\Throwable $e) {
            $this->printQueries();

            throw $e;
        }

        $end = \microtime(true);

        $this->logger->info('Last modified at :date', [
            ':date' => $response->getLastModified()->format(DateTimeImmutable::COOKIE),
        ]);

        $this->logger->info('Total SQL queries: :count', [
            ':count' => \Database_Query::getQueryCount(),
        ]);

        $this->logger->info('Executed in :time ms', [
            ':time' => \round(($end - $start) * 1000),
        ]);

        $this->printQueries();

        echo json_encode($response->getData(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT).PHP_EOL;
    }

    private function printQueries(): void
    {
        if (\function_exists('d')) {
            d(\Database_Query::getQueries());
        }
    }

    private function getCallArguments(ConsoleInputInterface $input): array
    {
        /** @see https://stackoverflow.com/a/30740680 */
        $fh = fopen('php://stdin', 'rb');
        stream_set_blocking($fh, false);
        $stdin = fgets($fh);

        if ($stdin) {
            return (array)json_decode($stdin, true, 5, \JSON_THROW_ON_ERROR);
        }

        $req = $input->getString(self::ARG_REQ);

        if ($req) {
            return (array)json_decode($req, true, 5, \JSON_THROW_ON_ERROR);
        }

        $names = [
            self::ARG_P1,
            self::ARG_P2,
            self::ARG_P3,
            self::ARG_P4,
        ];

        $output = [];

        foreach ($names as $name) {
            if (!$input->has($name)) {
                continue;
            }

            $output[] = match (true) {
                $input->isString($name) => $input->getString($name),
                $input->isInt($name) => $input->getInt($name),
                $input->isBool($name) => $input->getBool($name),
                default => throw new TaskException('Unknown argument type of option ":name"', [
                    ':name' => $name,
                ])
            };
        }

        return $output;
    }
}
