<?php
declare(strict_types=1);

namespace BetaKiller\Geo;

use Geocoder\Location;

final class QueryCondition implements QueryConditionInterface
{
    public const TYPE_BUILDING = 'building';
    public const TYPE_CITY     = 'city';
    public const TYPE_REGION   = 'region';
    public const TYPE_COUNTRY  = 'country';

    private const ALLOWED_TYPES = [
        self::TYPE_BUILDING,
        self::TYPE_CITY,
        self::TYPE_REGION,
        self::TYPE_COUNTRY,
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * Defines exact point with full address (street name and number)
     *
     * @return \BetaKiller\Geo\QueryConditionInterface
     */
    public static function building(): QueryConditionInterface
    {
        return new self(self::TYPE_BUILDING);
    }

    /**
     * Defines city
     *
     * @return \BetaKiller\Geo\QueryConditionInterface
     */
    public static function city(): QueryConditionInterface
    {
        return new self(self::TYPE_CITY);
    }

    /**
     * Defines country region (top level administrative division)
     *
     * @return \BetaKiller\Geo\QueryConditionInterface
     */
    public static function region(): QueryConditionInterface
    {
        return new self(self::TYPE_REGION);
    }

    /**
     * Defines country
     *
     * @return \BetaKiller\Geo\QueryConditionInterface
     */
    public static function country(): QueryConditionInterface
    {
        return new self(self::TYPE_COUNTRY);
    }

    /**
     * QueryCondition constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (!\in_array($type, self::ALLOWED_TYPES, true)) {
            throw new \LogicException(sprintf('Unknown query condition type "%s"', $type));
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isTypeCountry(): bool
    {
        return $this->type === self::TYPE_COUNTRY;
    }

    /**
     * @return bool
     */
    public function isTypeRegion(): bool
    {
        return $this->type === self::TYPE_REGION;
    }

    /**
     * @return bool
     */
    public function isTypeCity(): bool
    {
        return $this->type === self::TYPE_CITY;
    }

    /**
     * @return bool
     */
    public function isTypeBuilding(): bool
    {
        return $this->type === self::TYPE_BUILDING;
    }

    public function isValid(Location $location): bool
    {
//        d(
//            $location->toArray(),
//            $this->hasBuilding($location),
//            $this->hasCity($location),
//            $this->hasRegion($location),
//            $this->hasCountry($location)
//        );

        switch ($this->type) {
            case self::TYPE_BUILDING:
                return $this->hasBuilding($location)
// No city check for now (Prague addresses returns empty locality)
//                    && $this->hasCity($location)
                    && $this->hasRegion($location)
                    && $this->hasCountry($location);

            case self::TYPE_CITY:
                return !$this->hasBuilding($location)
                    && $this->hasCity($location)
                    && $this->hasRegion($location)
                    && $this->hasCountry($location);

            case self::TYPE_REGION:
                return !$this->hasBuilding($location)
                    && (self::isBothRegionAndCity($location) || !$this->hasCity($location))
                    && $this->hasRegion($location)
                    && $this->hasCountry($location);

            case self::TYPE_COUNTRY:
                return !$this->hasBuilding($location)
                    && !$this->hasCity($location)
                    && !$this->hasRegion($location)
                    && $this->hasCountry($location);

            default:
                throw new \LogicException(sprintf('Unknown type %s', $this->type));
        }
    }

    public static function isBothRegionAndCity(Location $point): bool
    {
        $adminLevels = $point->getAdminLevels();
        $regionName  = $adminLevels->count() > 0 ? $adminLevels->first()->getName() : null;
        $cityName    = $point->getLocality();

        if (!$cityName || !$regionName) {
            return false;
        }

        \similar_text($cityName, $regionName, $similarity);

        return $similarity >= 90;
    }

    private function hasBuilding(Location $location): bool
    {
        return $location->getStreetName() && $location->getStreetNumber();
    }

    private function hasCity(Location $location): bool
    {
        return (bool)$location->getLocality();
    }

    private function hasRegion(Location $location): bool
    {
        $levels = $location->getAdminLevels();

        if (!$levels->count()) {
            return false;
        }

        return (bool)$levels->first()->getName();
    }

    private function hasCountry(Location $location): bool
    {
        return (bool)$location->getCountry();
    }
}
