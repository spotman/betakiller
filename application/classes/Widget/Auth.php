<?php

use BetaKiller\Config\ConfigInterface;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_Auth extends \BetaKiller\IFace\Widget\AbstractBaseWidget
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
     * @throws WidgetException
     */
    public function getData()
    {
        // TODO DI
        $this->_config = \BetaKiller\DI\Container::getInstance()->get(ConfigInterface::class);

        $providers = $this->_config->load(['auth', 'providers']);

        if (!$providers) {
            throw new WidgetException('No auth providers specified in config');
        }

        return [
            'providers' =>  $providers,
        ];
    }
}
