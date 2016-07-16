<?php
namespace BetaKiller\Helper;

trait Base
{
    use Log;
    use IFace;

    protected function in_production($use_staging = FALSE)
    {
        return \Kohana::in_production($use_staging);
    }

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
