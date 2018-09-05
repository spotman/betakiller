<?php
namespace BetaKiller\Model;

use ORM;

class Language extends ORM
{
    public function getName(): ?string
    {
        return $this->loaded() ? $this->get('name') : null;
    }

    public function getLocale(): ?string
    {
        return $this->loaded() ? $this->get('locale') : null;
    }
}
