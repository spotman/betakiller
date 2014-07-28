<?php defined('SYSPATH') OR die('No direct script access.');


class Route extends Kohana_Route {

//    public static function by_uri($uri)
//    {
//        $all = static::all();
//
//        foreach ( $all as $route )
//        {
//            /** @var $route Route */
//            if ( $route->get_uri() == $uri )
//                return $route;
//        }
//
//        return NULL;
//    }

//    public static function set($name, $uri = NULL, $regex = NULL)
//    {
//        $exists = static::by_uri($uri);
//
//        // Throw an exception if one of existing routes is matching url
//        if ( $exists )
//            throw new Route_Exception('Route [:name] with uri [:uri] is overriding previously defined route [:duplicate] ',
//                array(':name' => $name, ':duplicate' => Route::name($exists), ':uri' => $uri));
//
//        return parent::set($name, $uri, $regex);
//    }

//    public function get_uri()
//    {
//        return $this->_uri;
//    }

}

class Route_Exception extends HTTP_Exception_500 {}