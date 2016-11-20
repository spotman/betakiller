<?php
namespace BetaKiller\IFace;

/**
 * Class IFaceProxy
 * @package BetaKiller\IFace
 * @deprecated
 */
abstract class IFaceProxy extends IFace
{
    protected $_proxy_iface;

    /**
     * @return IFace
     */
    abstract protected function get_proxy_iface();

    /**
     * IFaceProxy constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_proxy_iface = $this->get_proxy_iface();
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        // Empty data
        return [];
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->_proxy_iface->render();
    }
}
