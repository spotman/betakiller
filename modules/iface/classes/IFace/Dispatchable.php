<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Interface IFace_Dispatchable
 * @deprecated
 */
interface IFace_Dispatchable {

    /**
     * Parses provided uri part
     *
     * @param string $uri
     */
    public function parse_uri($uri);

    /**
     * If you need additional data for making uri, then create getters/setters an use them in this method
     *
     * @return string
     */
    public function make_uri();

}
