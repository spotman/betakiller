<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Interface ORM_Dispatchable
 * @deprecated
 */
interface ORM_Dispatchable {

    /**
     * Performs search of one item by its URI
     *
     * @param string $uri
     * @return ORM|NULL
     */
    public function filter_uri($uri);

}