<?php
namespace BetaKiller\Filter;

use BetaKiller\FilterInterface;

abstract class IDs extends Values {

    /**
     * @return string
     */
    protected function getArrayKey()
    {
        return 'ids';
    }

    /**
     * @param $value
     * @return $this
     */
    public function addValue($value)
    {
        return parent::addValue((int) $value);
    }

    /**
     * @return array
     */
    public function getIDs()
    {
        return $this->getValues();
    }

    public function getFirstID()
    {
        $ids = $this->getIDs();

        return count($ids) > 0
            ? $ids[0]
            : NULL;
    }

    /**
     * @param $id
     * @return $this
     */
    public function addID($id)
    {
        return parent::addValue($id);
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function addIDs(array $ids)
    {
        return parent::addValues($ids);
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function resetIDs(array $ids)
    {
        return parent::resetValues($ids);
    }

    public function hasID($id)
    {
        return parent::hasValue($id);
    }

}
