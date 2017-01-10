<?php
namespace BetaKiller\IFace;

// TODO \BetaKiller\IFace\Model\IFaceRelatedModel + base methods in IFace for collecting required url params from related models (recursively)

interface IFaceRelatedModelInterface
{
    /**
     * @return string
     */
    public function get_public_url();

    /**
     * @return string
     */
    public function get_admin_url();

    public function get_public_iface();
}
