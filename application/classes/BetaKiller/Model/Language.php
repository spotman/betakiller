<?php
namespace BetaKiller\Model;

use ORM;

class Language extends ORM
{
    public function getName()
    {
        return $this->loaded() ? $this->get('name') : null;
    }
}
