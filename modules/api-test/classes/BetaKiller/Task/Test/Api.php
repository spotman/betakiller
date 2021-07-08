<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Api\ApiFacade;
use BetaKiller\Model\UserInterface;
use BetaKiller\Task\AbstractTask;
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
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Api constructor.
     *
     * @param \BetaKiller\Api\ApiFacade       $api
     * @param \BetaKiller\Model\UserInterface $user
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(ApiFacade $api, UserInterface $user, LoggerInterface $logger)
    {
        $this->api    = $api;
        $this->user   = $user;
        $this->logger = $logger;

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
        return [
            self::ARG_TARGET => null,
            self::ARG_REQ    => null,
            self::ARG_P1     => null,
            self::ARG_P2     => null,
            self::ARG_P3     => null,
            self::ARG_P4     => null,
        ];
    }

    public function run(): void
    {
        $targetString = (string)$this->getOption(self::ARG_TARGET, true);

        $arguments = $this->getCallArguments();

        [$resourceName, $methodName] = explode('.', $targetString, 2);

        $this->logger->debug('Calling ":resource->:method" with arguments ":args"', [
            ':resource' => $resourceName,
            ':method'   => $methodName,
            ':args'     => json_encode($arguments),
        ]);

        $start = \microtime(true);

        \Database_Query::resetQueryCount();
        \Database_Query::enableQueryLog();

        $response = $this->api
            ->getResource($resourceName)
            ->call($methodName, $arguments, $this->user);

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

        if (\function_exists('d')) {
            d(\Database_Query::getQueries());
        }

        echo json_encode($response->getData(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT).PHP_EOL;
    }

    private function getCallArguments(): array
    {
        /** @see https://stackoverflow.com/a/30740680 */
        $fh = fopen('php://stdin', 'rb');
        stream_set_blocking($fh, false);
        $stdin = fgets($fh);

        if ($stdin) {
            return (array)json_decode($stdin, true, 5, \JSON_THROW_ON_ERROR);
        }

        $req = $this->getOption(self::ARG_REQ, false);

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
            $value = $this->getOption($name, false);

            if (is_numeric($value)) {
                $output[] = $value + 0;
            } elseif (in_array($value, ['true', 'false'])) {
                $output[] = $value === 'true';
            } elseif ($value && strpos($value, ',') !== false) {
                $output[] = explode(',', $value);
            } elseif ($value !== null) {
                $output[] = $value;
            }
        }

        return $output;
    }
}
