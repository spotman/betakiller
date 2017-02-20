<?php
namespace BetaKiller\Helper;

trait LangTrait
{
    private function current_lang()
    {
        return \I18n::lang();
    }
}
