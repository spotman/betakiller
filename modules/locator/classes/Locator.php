<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Model_IFace
 * @category   Locator
 * @author     Kohana Team
 * @package    Betakiller
 */
class Locator {

    use Singleton;

    /**
     * Initialize Kohana routes from locations
     */
    public static function init_routes()
    {
        self::instance()->process_layer();

        // TODO get all locations and cache them
        // TODO bulk add system routes from cache
    }

    protected function process_layer(Model_Location $parent_location = NULL, $url = NULL)
    {
        if ( $parent_location === NULL )
        {
            /** @var Model_Location $parent_location */
            $parent_location = ORM::factory("Location");
        }

        // Getting childs of current location
        $childs = $parent_location->get_childs();

        foreach ( $childs as $location )
        {
            // If this is default location, then add route for /
            if ( $location->is_default() )
            {
                $this->add_route($location, '/');
            }

            // Get base location url
            $location_url = $location->get_url();

            // Let`s make full url
            $location_url = rtrim($url, '/') .'/'. $location_url;


            // Adding route for current location
            $this->add_route($location, $location_url);

            // Going deep
            $this->process_layer($location, $location_url);
        }
    }

    /**
     * Add system route for location
     * @param Model_Location    $location
     * @param string $full_url  Full route url
     */
    protected function add_route(Model_Location $location, $full_url)
    {
        // Removing starting slash
        $full_url = ltrim($full_url, '/');

        // Making route name (it must be unique and relate to location _codename)
        $route_name = ( $full_url == '' ) ? 'index' : $location->get_codename();

        // Adding route
        Route::set($route_name, $full_url)
            ->defaults( $this->get_route_defaults($location) );
    }

    protected function get_route_defaults(Model_Location $location)
    {
        return array(
            'controller'        => 'IFace',
            'location'          => $location,
            'action'            => 'index',
        );
    }

    /**
     * Method returns location model for current request
     * @param Request|NULL $request
     * @return Model_Location
     * @throws Locator_Exception
     */
    public static function get_route_location_model(Request $request = NULL)
    {
        $request = $request ?: Request::current();

        /** @var Route $route */
        $route = $request->route();

        $defaults = (object) $route->defaults();
        $location = $defaults->location;

        if ( ! ($location instanceof Model_Location) )
            throw new Locator_Exception('Incorrect Location model in route [:route]',
                array(':route' => Route::name($route))
            );

        return $location;
    }

}

class Locator_Exception extends HTTP_Exception_500 {}