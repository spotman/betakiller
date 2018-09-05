<?php
namespace BetaKiller\Model;

use ORM;

class Language extends ORM implements LanguageInterface
{
    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->loaded() ? $this->get('name') : null;
    }

    /**
     * @return null|string
     */
    public function getLocale(): ?string
    {
        return $this->loaded() ? $this->get('locale') : null;
    }
}
