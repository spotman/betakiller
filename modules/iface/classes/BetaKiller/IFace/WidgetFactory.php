<?php
namespace BetaKiller\IFace;

use BetaKiller\DI\Container;
use BetaKiller\Utils\Instance\Cached;
use Request;
use Response;

class WidgetFactory
{
    use Cached;

    /**
     * @param                $name
     * @param \Request|NULL  $request
     * @param \Response|NULL $response
     *
     * @return Widget
     */
    public function create($name, Request $request = NULL, Response $response = NULL)
    {
        $class_name = static::get_class_prefix() . $name;

        // Getting current request if none provided
        $request = $request ?: Request::current();

        // Creating empty response if none provided
        $response = $response ?: Response::factory();

        if (!class_exists($class_name)) {
            $class_name = \Widget_Default::class;
        }

        return Container::instance()->make($class_name, [
            'name'      =>  $name,
            'request'   =>  $request,
            'response'  =>  $response,
        ]);
    }

    protected static function get_class_prefix()
    {
        return 'Widget_';
    }
}
