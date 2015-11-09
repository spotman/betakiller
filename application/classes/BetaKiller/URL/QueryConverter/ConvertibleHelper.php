<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 09.11.15
 * Time: 14:36
 */

namespace BetaKiller\URL\QueryConverter;

use BetaKiller\URL\QueryConverter;

trait ConvertibleHelper
{
    public function toUrlQueryArray()
    {
        return $this->queryConverterFactory()
            ->toQueryArray($this->getUrlQueryConverterConvertible());
    }

    public function toUrlQueryString()
    {
        return $this->queryConverterFactory()
            ->toQueryString($this->getUrlQueryConverterConvertible());
    }

    public function fromUrlQueryArray(array $query)
    {
        return $this->queryConverterFactory()
            ->fromQueryArray($query, $this->getUrlQueryConverterConvertible());
    }

    protected function queryConverterFactory()
    {
        //TODO DI
        return QueryConverter::instance();
    }

    /**
     * @return Convertible
     */
    abstract protected function getUrlQueryConverterConvertible();

}
