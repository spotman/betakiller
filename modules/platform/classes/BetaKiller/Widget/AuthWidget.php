<?php
namespace BetaKiller\Widget;

use BetaKiller\Config\ConfigProviderInterface;

class AuthWidget extends AbstractBaseWidget
{
    public const PROVIDER_REGULAR = 'regular';
    public const PROVIDER_ULOGIN  = 'uLogin';

    /**
     * @var ConfigProviderInterface
     * @Inject
     */
    private $config;

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws WidgetException
     */
    public function getData(): array
    {
        $providers = $this->config->load(['auth', 'providers']);

        if (!$providers) {
            throw new WidgetException('No auth providers specified in config');
        }

        return [
            'providers' => $providers,
        ];
    }
}
