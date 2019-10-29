<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Geo\QueryCondition;
use BetaKiller\Geo\QueryConditionInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Psr\Log\LoggerInterface;
use Worknector\Service\GeoService;

final class GeoCoder extends AbstractTask
{
    /**
     * @var \Worknector\Service\GeoService
     */
    private $geo;

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
     * @param \Worknector\Service\GeoService  $geo
     * @param \BetaKiller\Model\UserInterface $user
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(GeoService $geo, UserInterface $user, LoggerInterface $logger)
    {
        $this->geo    = $geo;
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
            'address' => null,
            'lat'     => null,
            'lon'     => null,
            'type'    => QueryCondition::TYPE_BUILDING,
        ];
    }

    public function run(): void
    {
        $type    = (string)$this->getOption('type');
        $address = (string)$this->getOption('address', false);
        $lat     = (float)$this->getOption('lat', false);
        $lon     = (float)$this->getOption('lon', false);

        $cond = new QueryCondition($type);
        $lang = $this->user->getLanguage();

        switch (true) {
            case $address:
                $this->geocode($address, $cond, $lang);
                break;

            case $lat && $lon:
                $this->reverse($lat, $lon, $cond, $lang);
                break;

            default:
                throw new TaskException('Define address or lat/lon');
        }
    }

    private function geocode(string $address, QueryConditionInterface $condition, LanguageInterface $lang): void
    {
        $result = $this->geo->directQueryAll($address, $condition, $lang);

        $this->displayResult($result, $condition);
    }

    private function reverse(float $lat, float $lon, QueryConditionInterface $condition, LanguageInterface $lang): void
    {
        $result = $this->geo->reverseQueryAll($lat, $lon, $condition, $lang);

        $this->displayResult($result, $condition);
    }

    /**
     * @param \Geocoder\Location[]                    $result
     * @param \BetaKiller\Geo\QueryConditionInterface $condition
     *
     * @throws \Worknector\Service\ServiceException
     */
    private function displayResult(array $result, QueryConditionInterface $condition): void
    {
        if (!count($result)) {
            $this->logger->info('No results found');
        }

        foreach ($result as $location) {
            $coords = $location->getCoordinates();

            if (!$coords) {
                continue;
            }

            $this->logger->info('[:lat :lon] :label', [
                ':lat'   => $coords->getLatitude(),
                ':lon'   => $coords->getLongitude(),
                ':label' => $this->geo->formatLocation($location, $condition, true),
            ]);

            $levels = [];

            foreach ($location->getAdminLevels()->all() as $item) {
                $levels[] = [
                    'code' => $item->getCode(),
                    'name' => $item->getName(),
                ];
            }

            $this->logger->debug('Admin levels: :levels', [
                ':levels' => \json_encode($levels, \JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
}
