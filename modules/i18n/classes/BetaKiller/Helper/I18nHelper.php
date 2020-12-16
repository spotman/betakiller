<?php
namespace BetaKiller\Helper;

use BetaKiller\Model\LanguageInterface;

class I18nHelper implements RequestLanguageHelperInterface
{
    /**
     * @var LanguageInterface
     */
    private LanguageInterface $lang;

    /**
     * I18nHelper constructor.
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     */
    public function __construct(LanguageInterface $lang)
    {
        $this->lang = $lang;
    }

    public function getLang(): LanguageInterface
    {
        return $this->lang;
    }

    public function setLang(LanguageInterface $value): void
    {
        $this->lang = $value;
    }
}
