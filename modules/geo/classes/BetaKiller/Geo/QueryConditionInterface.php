<?php
declare(strict_types=1);

namespace BetaKiller\Geo;

use Geocoder\Location;

interface QueryConditionInterface
{
    /**
     * Defines exact point with full address (street name and number)
     *
     * @return \BetaKiller\Geo\QueryConditionInterface
     */
    public static function building(): QueryConditionInterface;

    /**
     * Defines city
     *
     * @return \BetaKiller\Geo\QueryConditionInterface
     */
    public static function city(): QueryConditionInterface;

    /**
     * Defines country region (top level administrative division)
     *
     * @return \BetaKiller\Geo\QueryConditionInterface
     */
    public static function region(): QueryConditionInterface;

    /**
     * Defines country
     *
     * @return \BetaKiller\Geo\QueryConditionInterface
     */
    public static function country(): QueryConditionInterface;

    /**
     * @param \Geocoder\Location $location
     *
     * @return bool
     */
    public function isValid(Location $location): bool;

    /**
     * @param \Geocoder\Location $point
     *
     * @return bool
     */
    public static function isBothRegionAndCity(Location $point): bool;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public function isTypeCountry(): bool;

    /**
     * @return bool
     */
    public function isTypeRegion(): bool;

    /**
     * @return bool
     */
    public function isTypeCity(): bool;

    /**
     * @return bool
     */
    public function isTypeBuilding(): bool;
}
