<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_IFace {

    /**
     * @var string
     */
    protected $codename;

    /**
     * @var Model_Location
     */
    protected $location;

    /**
     * @param $location Model_Location
     * @return IFace
     */
    public static function factory(Model_Location $location)
    {
        $codename = $location->get_codename();
        $class_name = 'IFace_'. $codename;

        if ( ! class_exists($class_name) )
        {
            $class_name = 'IFace_Default';
        }

        /** @var IFace $object */
        $object = new $class_name($codename);

        $object->location($location);
        return $object;
    }

    public function __construct($codename)
    {
        $this->codename = $codename;
    }

    public function location(Model_Location $location = NULL)
    {
        // Act as a getter
        if ( ! $location )
            return $this->location;

        // Act s a setter
        return ( $this->location = $location );
    }


    public function action_index()
    {
        return $this->render();
    }

    public function render()
    {
        $view = $this->get_view();
        return $view;
    }

    protected function get_view()
    {
        $view_path = 'iface'. DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $this->codename);

        return $this->view_factory($view_path);
    }

    private function view_factory($path)
    {
        return View::factory($path);
    }
}