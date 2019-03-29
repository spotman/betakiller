<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Model\UserInterface;
use BetaKiller\Task\AbstractTask;
use Geocoder\Formatter\StringFormatter;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Psr\Log\LoggerInterface;

class GeoCoder extends AbstractTask
{
    /**
     * @var \Geocoder\Provider\Provider
     */
    private $provider;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * GeoCoder constructor.
     *
     * @param \Geocoder\Provider\Provider $provider
     * @param \Psr\Log\LoggerInterface    $logger
     */
    public function __construct(Provider $provider, UserInterface $user, LoggerInterface $logger)
    {
        $this->provider = $provider;
        $this->user = $user;
        $this->logger   = $logger;

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
            'address' => null,
        ];
    }

    public function run(): void
    {
        $address = (string)$this->getOption('address', true);

        $locale = $this->user->getLanguage()->getLocale();

        $query = GeocodeQuery::create($address)
            ->withLocale($locale)
            ->withLimit(10);

        $result = $this->provider->geocodeQuery($query);

        if (!$result->count()) {
            $this->logger->info('No results found');
        }

        $formatter = new StringFormatter();

        foreach ($result->all() as $location) {
            $label = $formatter->format($location, '%S %n, %z %L, %C');

            $this->logger->info($label);
        }
    }
}
