<?php
namespace BetaKiller\Helper;

trait Lang
{
    private function current_lang()
    {
        return \I18n::lang();
    }
}
