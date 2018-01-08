<?php
namespace BetaKiller\IFace\Exception;

use BetaKiller\IFace\IFaceInterface;
use HTTP_Exception_404;

class IFaceMissingUrlException extends HTTP_Exception_404
{
    /**
     * @param string                          $url_part
     * @param \BetaKiller\IFace\IFaceInterface $parent_iface
     */
    public function __construct($url_part, IFaceInterface $parent_iface = null)
    {
        // @TODO custom 404 handlers for IFaces with childs (category can show friendly message if unknown staff was requested)

        // @TODO custom view from parent iface

        parent::__construct('Unknown url part :part', [':part' => $url_part]);
    }
}
