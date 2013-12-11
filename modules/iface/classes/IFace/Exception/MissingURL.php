<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Exception_MissingURL extends IFace_Exception {

    /**
    * @var   integer    HTTP 404 Not found
    */
    protected $_code = 404;

    /**
     * @param string $url_part
     * @param IFace_Model $parent_iface_model
     */
    public function __construct($url_part, IFace_Model $parent_iface_model = NULL)
    {
        // @TODO custom 404 handlers for IFaces with childs (category can show friendly message if unknown staff was requested)

        // @TODO custom view from parent iface

        parent::__construct('Unknown url part :part', array(':part' => $url_part));
    }

}