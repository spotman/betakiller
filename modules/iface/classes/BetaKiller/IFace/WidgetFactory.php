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

        return new $class_name($name, $request, $response);
    }

    protected static function get_class_prefix()
    {
        return 'Widget_';
    }

    /**
     * Creates instance of IFace from model
     *
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     * @return IFace
     */
    public function from_model(\BetaKiller\IFace\IFaceModelInterface $model)
    {
        return $this->get_provider()->from_model($model);
    }

    /**
     * Creates IFace instance from it`s codename
     *
     * @param string $codename IFace codename
     * @return IFace
     * @throws \IFace_Exception
     */
    public function from_codename($codename)
    {
        return $this->get_provider()->by_codename($codename);
    }

    protected function get_provider()
    {
        return Container::instance()->get(\IFace_Provider::class);
    }
}
