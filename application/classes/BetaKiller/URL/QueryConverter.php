<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 02.11.15
 * Time: 15:45
 */

namespace BetaKiller\URL;

use BetaKiller\URL\QueryConverter\Convertible;
use BetaKiller\URL\QueryConverter\ConvertibleItem;
use BetaKiller\URL\QueryConverter\Exception;
use BetaKiller\Utils;

class QueryConverter
{
    use Utils\Instance\Simple;

    protected $_valuesSeparator = ',';
    protected $_nsSeparator = '-';

    public function fromQueryArray(array $query, Convertible $obj, array $allowedKeys = null)
    {
        if (!$allowedKeys) {
            $allowedKeys = $this->getAllowedUrlQueryKeys($obj);
        }

        $ns = $obj->getUrlQueryKeysNamespace();

        foreach ($query as $key => $concatValues) {
            // Remove namespace from key
            if ($ns) {
                $key = str_replace($ns.$this->_nsSeparator, '', $key);
            }

            // Skip unknown keys
            if (!in_array($key, $allowedKeys))
                continue;

            // Convert values string to array
            $values = explode($this->_valuesSeparator, $concatValues);

            if (!count($values))
                throw new Exception('No values provided in query part for [:key] key', [':key' => $key]);

            // Process values
            $values = array_map(array($this, 'parseValue'), $values);

            // Make item
            $item = $obj->getItemByQueryKey($key);

            // Store key and values
            $item->setUrlQueryKey($key);
            $item->setUrlQueryValues($values);
        }
    }

    protected function getAllowedUrlQueryKeys(Convertible $obj)
    {
        $keys = [];

        foreach ($obj as $item) { /** @var $item ConvertibleItem */
            if (!$item->isUrlConversionAllowed())
                continue;

            $keys[] = $item->getUrlQueryKey();
        }

        return $keys;
    }

    public function toQueryArray(Convertible $obj)
    {
        $result = [];

        $ns = $obj->getUrlQueryKeysNamespace();

        foreach ($obj as $item) { /** @var $item ConvertibleItem */

            // Skip non-convertible items
            if (!$item->isUrlConversionAllowed())
                continue;

            $key = $item->getUrlQueryKey();
            $values = $item->getUrlQueryValues();

            // Skip empty values
            if ($values === null)
                continue;

            if (!is_array($values)) {
                $values = [$values];
            }

            // Process values
            $values = array_map(array($this, 'makeValue'), $values);

            if ($values) {
                // Add namespace to url key
                if ($ns) {
                    $key = $ns.$this->_nsSeparator.$key;
                }

                // Store values
                $result[$key] = implode($this->_valuesSeparator, $values);
            }
        }

        return $result;
    }

    public function toQueryString(Convertible $obj)
    {
        $arr = $this->toQueryArray($obj);

        return http_build_query($arr);
    }

    protected function parseValue($string)
    {
        if ($string == 'true') {
            return true;
        } elseif ($string == 'false') {
            return false;
        } elseif ($string == 'null') {
            return null;
        } else {
            return $string;
        }
    }

    protected function makeValue($value)
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_null($value)) {
            return 'null';
        } elseif (is_scalar($value)) {
            return (string) $value;
        } else {
            throw new Exception('Only scalar values allowed');
        }
    }

}
