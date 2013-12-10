<?php defined('SYSPATH') OR die('No direct script access.');

abstract class IFace_Provider_Source implements IFace_Provider_Interface {

    use Util_Factory;

    /**
     * @param string $uri
     * @param IFace_Model|null $parent_model
     * @return IFace_Model
     * @throws IFace_Exception
     */
    final public function by_uri($uri, IFace_Model $parent_model = NULL)
    {
        throw new IFace_Exception('Use IFace_Provider::by_url() method instead');
    }

}