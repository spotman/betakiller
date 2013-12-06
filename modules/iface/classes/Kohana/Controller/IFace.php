<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Controller_IFace extends Controller_Basic {

    /**
     * @var IFace
     */
    protected $iface = NULL;

    /**
     * @var Model_Location
     */
    protected $location = NULL;

    public function before()
    {
        $location = $this->get_location();

        // If this is default location and client requested non-slash uri, redirect client to /
        if ( $location->is_default() AND Request::current()->detect_uri() != '' )
        {
            $this->redirect('/');
        }

        parent::before();
    }

    public function get_proxy_object()
    {
        $object = $this->get_iface();

        if ( ! ($object instanceof Iface) )
            throw new Kohana_Exception('IFace controller can not serve objects which are not instance of class IFace');

        return $object;
    }

    /**
     * Returns interface linked to current route
     * @TODO move to Env or another factory
     * @return IFace|null
     */
    protected function get_iface()
    {
        if ( ! $this->iface )
        {
            $this->iface = IFace::factory($this->get_location()->get_codename());
        }

        return $this->iface;
    }

    /**
     * @return Model_Location
     */
    protected function get_location()
    {
        if ( ! $this->location )
        {
            $this->location = Locator::get_route_location_model($this->request);
        }

        return $this->location;
    }

}