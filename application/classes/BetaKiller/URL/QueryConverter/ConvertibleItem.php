<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 02.11.15
 * Time: 15:53
 */

namespace BetaKiller\URL\QueryConverter;


interface ConvertibleItem
{
    /**
     * Returns item`s string key for url query part
     *
     * @return string
     */
    public function getUrlQueryKey();

    /**
     * Returns item`s scalar value (string, int, float) or array of values for using in url query part value
     *
     * @return int|float|string|int[]|float[]|string[]
     */
    public function getUrlQueryValues();

    /**
     * Store query key into item
     *
     * @param string $value
     */
    public function setUrlQueryKey($value);

    /**
     * Store array of values into item
     *
     * @param array $values
     */
    public function setUrlQueryValues(array $values);

    /**
     * Returns true if current item is usable for url converting
     * @return bool
     */
    public function isUrlConversionAllowed();
}
