<?php

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\Widget\WidgetException;

class Widget_Auth extends AbstractBaseWidget
{
    public const PROVIDER_REGULAR = 'regular';
    public const PROVIDER_ULOGIN  = 'uLogin';

    /**
     * @var ConfigProviderInterface
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
            'providers' => $providers,
        ];
    }
}
