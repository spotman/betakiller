<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Controller_IFace extends Controller_Template {

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

    // TODO
    public function after()
    {
        if ( $this->_proxy_result )
        {
            $content_type = $this->iface->content_type();

            switch ( $content_type )
            {
                case IFace::CONTENT_TYPE_HTML:
                    $response = $this->_proxy_result;
                break;

                case IFace::CONTENT_TYPE_JSON:
                    $response = json_encode($this->_proxy_result);
                break;

                default:
                    throw new HTTP_Exception_500('Unknown IFace content type: :type', array(':type' => $content_type));
            }


            $this->template->set_content($response);
        }
        else
        {
            $this->template = NULL;
        }
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
            $this->iface = IFace::factory($this->get_location());
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