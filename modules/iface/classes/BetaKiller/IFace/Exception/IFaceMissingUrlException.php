<?php
namespace BetaKiller\IFace\Exception;

use BetaKiller\IFace\IFaceModelInterface;
use HTTP_Exception_404;

class IFaceMissingUrlException extends HTTP_Exception_404
{
    /**
     * @param string                                $urlPart
     * @param \BetaKiller\IFace\IFaceModelInterface $parentIFaceModel
     */
    public function __construct(string $urlPart, IFaceModelInterface $parentIFaceModel = null)
    {
        // @TODO custom 404 handlers for IFaces with childs (category can show friendly message if unknown staff was requested)

        // @TODO custom view from parent iface

        parent::__construct('Unknown url part :part', [':part' => $urlPart]);
    }
}
