<?php
namespace BetaKiller\Filter\Model;

class ValuesGroup
{
    /**
     * @var string
     */
    protected $_codename;

    /**
     * @var string
     */
    protected $_label;

    /**
     * @var Value[]
     */
    protected $_values;

    /**
     * @param string    $_label
     * @param array     $_values
     * @return static
     */
    public static function factory($_label, array $_values) //$_codename,
    {
        return new static($_label, $_values);
    }

    /**
     * @param string    $_label
     * @param Value[]   $_values
     */
    public function __construct($_label, array $_values)
    {
//        $this->_codename    = $_codename;
        $this->_label       = $_label;
        $this->_values      = $_values;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @return Value[]
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * @param \BetaKiller\Filter\Model\Value $value
     */
    public function addValue(Value $value)
    {
        $this->_values[] = $value;
    }

    /**
     * @param string $nsSeparator
     * @param bool   $selectedOnly
     * @return array
     */
    public function asValuesArray($nsSeparator = '-', $selectedOnly = false)
    {
        $output = [];

        foreach ($this->getValues() as $value) {
            if ($selectedOnly AND !$value->isSelected())
                continue;

            $keyPrefix = $value->getKeyNamespace();
            $key = $keyPrefix
                ? $keyPrefix.$nsSeparator.$value->getKey()
                : $value->getKey();

            $output[$key] = $value->getLabel();
        }

        return $output;
    }

    /**
     * @return string
     */
    public function getCodename()
    {
        return $this->_codename;
    }

    /**
     * @param string $codename
     */
    public function setCodename($codename)
    {
        $this->_codename = $codename;
    }

}
