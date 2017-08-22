<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 02.11.15
 * Time: 15:45
 */

namespace BetaKiller\URL;

use BetaKiller\URL\QueryConverter\ConvertibleInterface;
use BetaKiller\URL\QueryConverter\ConvertibleItemInterface;
use BetaKiller\URL\QueryConverter\Exception;
use BetaKiller\Utils;

class QueryConverter
{
    use Utils\Instance\Simple;

    protected $_valuesSeparator = ',';
    protected $_nsSeparator = '-';

    /**
     * Apply url query parts array to ConvertibleInterface object
     *
     * @param array                                               $query
     * @param \BetaKiller\URL\QueryConverter\ConvertibleInterface $obj
     * @param array|null                                          $allowedKeys Optional allowed keys array
     *
     * @return \BetaKiller\URL\QueryConverter\ConvertibleInterface
     * @throws \BetaKiller\URL\QueryConverter\Exception
     */
    public function fromQueryArray(array $query, ConvertibleInterface $obj, array $allowedKeys = null)
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
            if (!in_array($key, $allowedKeys, true)) {
                continue;
            }

            // Convert values string to array
            $values = explode($this->_valuesSeparator, $concatValues);

            if (!count($values)) {
                throw new Exception('No values provided in query part for [:key] key', [':key' => $key]);
            }

            // Process values
            $values = array_map(array($this, 'parseValue'), $values);

            // Make item
            $item = $obj->getItemByQueryKey($key);

            // Store key and values
            $item->setUrlQueryKey($key);
            $item->setUrlQueryValues($values);
        }

        return $obj;
    }

    /**
     * Returns array of keys allowed for url conversion
     *
     * @param \BetaKiller\URL\QueryConverter\ConvertibleInterface $obj
     * @return array
     */
    protected function getAllowedUrlQueryKeys(ConvertibleInterface $obj): array
    {
        $keys = [];

        foreach ($obj as $item) { /** @var $item ConvertibleItemInterface */
            if (!$item->isUrlConversionAllowed()) {
                continue;
            }

            $keys[] = $item->getUrlQueryKey();
        }

        return $keys;
    }

    /**
     * Converts ConvertibleInterface object to URL query parts array
     *
     * @param \BetaKiller\URL\QueryConverter\ConvertibleInterface $obj
     * @return array
     */
    public function toQueryArray(ConvertibleInterface $obj): array
    {
        $result = [];

        $ns = $obj->getUrlQueryKeysNamespace();

        foreach ($obj as $item) { /** @var $item ConvertibleItemInterface */

            // Skip non-convertible items
            if (!$item->isUrlConversionAllowed())
                continue;

            $key = $item->getUrlQueryKey();
            $values = $item->getUrlQueryValues();

            // Skip empty values
            if ($values === null) {
                continue;
            }

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

    /**
     * Converts ConvertibleInterface object to fully qualified URL query string
     *
     * @param \BetaKiller\URL\QueryConverter\ConvertibleInterface $obj
     * @return string
     */
    public function toQueryString(ConvertibleInterface $obj): string
    {
        $arr = $this->toQueryArray($obj);

        return http_build_query($arr);
    }

    /**
     * Converts string value representation to its actual value
     *
     * @param $string
     * @return bool|null
     */
    protected function parseValue($string): ?bool
    {
        if ($string === 'true') {
            return true;
        } elseif ($string === 'false') {
            return false;
        } elseif ($string === 'null') {
            return null;
        } else {
            return $string;
        }
    }

    /**
     * Converts value to its string representation
     *
     * @param $value
     * @return string
     * @throws \BetaKiller\URL\QueryConverter\Exception
     */
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
