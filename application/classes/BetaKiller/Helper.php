<?php
namespace BetaKiller;

trait Helper
{
    use Helper\Log;

    protected function current_lang()
    {
        return \I18n::lang();
    }

    protected function current_user($allow_guest = FALSE)
    {
        return \Env::user($allow_guest);
    }

    /**
     * @param string $group
     * @param null $default
     * @return \Config_Group|string|int|bool|null
     * @throws \Kohana_Exception
     */
    protected static function config($group, $default = NULL)
    {
        return \Kohana::$config->load($group) ?: $default;
    }
}
