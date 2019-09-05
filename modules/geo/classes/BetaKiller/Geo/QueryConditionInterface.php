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
     * @return string
     */
    public function getType(): string;
}
