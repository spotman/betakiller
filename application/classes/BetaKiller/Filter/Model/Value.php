<?php
namespace BetaKiller\Filter\Model;

class Value
{
//    /**
//     * @var string
//     */
//    protected $_parameterCodename;

    /**
     * @var string
     */
    protected $_key;

    /**
     * @var string
     */
    protected $_label;

    /**
     * @var bool
     */
    protected $_selected = FALSE;

    public static function factory($_key, $_label, $_selected = FALSE)
    {
        return new static($_key, $_label, $_selected);
    }

    /**
     * Value constructor
     *
     * @param $_key
     * @param $_label
     * @param bool $_selected
     */
    public function __construct($_key, $_label, $_selected = FALSE)
    {
//        $this->_parameterCodename   = $_parameter;
        $this->_key                 = $_key;
        $this->_label               = $_label;
        $this->_selected            = $_selected;
    }

//    /**
//     * @return string
//     */
//    public function getParameterCodename()
//    {
//        return $this->_parameterCodename;
//    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @return boolean
     */
    public function isSelected()
    {
        return $this->_selected;
    }

}
