<?php defined('SYSPATH') OR die('No direct script access.');

class Core_IFace_Exception_MissingURL extends HTTP_Exception_404 {

    /**
     * @param string $url_part
     * @param BetaKiller\IFace\IFaceInterface $parent_iface
     */
    public function __construct($url_part, BetaKiller\IFace\IFaceInterface $parent_iface = NULL)
    {
        // @TODO custom 404 handlers for IFaces with childs (category can show friendly message if unknown staff was requested)

        // @TODO custom view from parent iface

        parent::__construct('Unknown url part :part', array(':part' => $url_part));
    }

}
