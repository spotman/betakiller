<?php
namespace BetaKiller\Filter;

abstract class ID extends AbstractFilter {

    protected $_id = null;

    /**
     * @param array $data
     * @return static
     */
    public function fromArray(array $data)
    {
        $this->_id = isset($data['id'])
            ? (int) $data['id']
            : NULL;

        return $this;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array('id' => $this->_id);
    }

    public function getID()
    {
        return $this->_id;
    }

    public function setID($id)
    {
        $this->_id = (int) $id;
    }

    /**
     * Returns TRUE if filter was previously selected (optional filtering via key)
     *
     * @param string|int|null $value
     * @return bool
     */
    public function isSelected($value = null)
    {
        $current = $this->getID();

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
        $this->setID(array_pop($values));
    }

    public function getUrlQueryValues()
    {
        return $this->getID();
    }

}
