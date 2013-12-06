<?php defined('SYSPATH') OR die('No direct script access.');

abstract class IFace_Model {

    use Util_GetterAndSetterMethod;

    protected $_provider;

    // TODO

    public function provider(IFace_Provider $provider)
    {
        return $this->getter_and_setter_method('_provider', $provider);
    }
}