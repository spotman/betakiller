<?php
namespace BetaKiller\Filter;

abstract class Boolean extends Value {

    public function setValue($value)
    {
        parent::setValue((bool) $value);
    }

    public function isEnabled()
    {
        return (bool) $this->getValue();
    }

    public function enable()
    {
        $this->setValue(TRUE);
    }

    public function disable()
    {
        $this->setValue(FALSE);
    }

    /**
     * Returns TRUE if provided key was previously selected
     *
     * @param string|int|null $value
     * @return bool
     */
    public function isSelected($value = NULL): bool
    {
        return ($this->getValue() !== null);
    }

    public function getUrlQueryValues()
    {
        return $this->getValue();
    }

    /**
     * Returns array of values with structure like <key> => <label>
     *
     * @param string|null $filterHaving
     * @return array
     */
    protected function getAvailableValuesPairs($filterHaving = NULL)
    {
        // No list for bool filters are available
        return [];
    }

    /**
     * Returns array of values with structure like <key> => <label>
     *
     * @return array
     */
    protected function getSelectedValuesPairs()
    {
        // No list for bool filters are available
        return [];
    }

}
