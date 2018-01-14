<?php

/**
 * Class Kohana
 * @deprecated
 */
class Kohana extends Kohana_Core {

    public static $environmentString = 'development';

    public static function inProduction(?bool $useStaging = null)
    {
        $values = $useStaging
            ? [Kohana::PRODUCTION, Kohana::STAGING]
            : [Kohana::PRODUCTION];

        return in_array(Kohana::$environment, $values, true);
    }

    /**
     * @param $file
     *
     * @return \Kohana_Config_Group
     * @throws \Kohana_Exception
     * @deprecated Use ConfigProviderInterface
     */
    public static function config($file)
    {
        return Kohana::$config->load($file);
    }
}
