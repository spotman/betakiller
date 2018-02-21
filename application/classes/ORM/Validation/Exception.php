<?php
/**
 * ORM Validation exceptions.
 *
 * @package    Kohana/ORM
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class ORM_Validation_Exception extends Kohana_ORM_Validation_Exception
{
    public function getValidationObject(): Validation
    {
        return $this->_objects['_object'];
    }

    public function getFormattedErrors(): array
    {
        return $this->errors('orm');
    }
}
