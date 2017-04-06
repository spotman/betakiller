<?php

use BetaKiller\Config\ConfigInterface;
use BetaKiller\IFace\Widget\Exception;

class Widget_Auth extends \BetaKiller\IFace\Widget
{
    const PROVIDER_REGULAR = 'regular';
    const PROVIDER_ULOGIN = 'uLogin';

    /**
     * @var ConfigInterface
     * @Inject
     */
    private $_config;

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws Exception
     */
    public function get_data()
    {
        // TODO DI
        $this->_config = \BetaKiller\DI\Container::instance()->get(ConfigInterface::class);

        $providers = $this->_config->load(['auth', 'providers']);

        if (!$providers) {
            throw new Exception('No auth providers specified in config');
        }

        return [
            'providers' =>  $providers,
        ];
    }
}
