<?php
namespace BetaKiller\Filter;

abstract class Value extends AbstractFilter {

    protected $_value = NULL;

    /**
     * @param array $data
     * @return static
     */
    public function fromArray(array $data)
    {
        if ( isset($data['value']) )
        {
            $this->setValue($data['value']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array('value' => $this->getValue());
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Returns TRUE if filter was previously selected (optional filtering via key)
     *
     * @param string|int|null $value
     * @return bool
     */
    public function isSelected($value = null)
    {
        $current = $this->getValue();

        if ($value !== null) {
            return ($current == $value);
        } elseif ($current !== null) {
            return (bool) $current;
        } else {
            return null;
        }
    }

    public function setUrlQueryValues(array $values)
    {
        $this->setValue(array_pop($values));
    }

    public function getUrlQueryValues()
    {
        return $this->getValue();
    }

}
