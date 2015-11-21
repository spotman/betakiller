<?php
namespace BetaKiller\Filter;

abstract class Values extends Base {

    protected $_values = array();

    /**
     * @param array $data
     * @return static
     */
    public function fromArray(array $data)
    {
        $key = $this->getArrayKey();

        if ( isset($data[$key]) )
        {
            $this->addValues($data[$key]);
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getArrayKey()
    {
        return 'values';
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array($this->getArrayKey() => $this->getValues());
    }

    public function getValues()
    {
        return array_unique($this->_values);
    }

    public function addValue($value)
    {
        $this->_values[] = $value;

        return $this;
    }

    public function addValues(array $values)
    {
        foreach ( array_unique($values) as $value )
        {
            $this->addValue($value);
        }

        return $this;
    }

    public function resetValues(array $values)
    {
        $this->_values = array();

        return $this->addValues($values);
    }

    /**
     * Returns TRUE if filter was previously selected (optional filtering via key)
     *
     * @param string|int|null $value
     * @return bool
     */
    public function isSelected($value = null)
    {
        if ($value === null)
            return (count($this->getValues()) > 0);
        else
            return $this->hasValue($value);
    }

    public function hasValue($value)
    {
        return in_array($value, $this->getValues());
    }

    public function setUrlQueryValues(array $values)
    {
        $this->addValues($values);
    }

    public function getUrlQueryValues()
    {
        return $this->getValues();
    }

}
