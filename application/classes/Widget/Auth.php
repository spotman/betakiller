<?php

use BetaKiller\Config\ConfigInterface;
use BetaKiller\IFace\Widget\WidgetException;
use BetaKiller\IFace\Widget\AbstractBaseWidget;

class Widget_Auth extends AbstractBaseWidget
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
    public function getData(): array
    {
        $providers = $this->_config->load(['auth', 'providers']);

        if (!$providers) {
            throw new WidgetException('No auth providers specified in config');
        }

        return [
            'providers' =>  $providers,
        ];
    }
}
