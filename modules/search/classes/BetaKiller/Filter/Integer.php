<?php
namespace BetaKiller\Filter;

abstract class Integer extends Value {

    public function setValue($value)
    {
        parent::setValue((int) $value);
    }

}
