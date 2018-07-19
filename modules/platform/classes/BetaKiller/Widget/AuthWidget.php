<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Config\ConfigProviderInterface;

class AuthWidget extends AbstractPublicWidget
{
    public const PROVIDER_REGULAR = 'regular';
    public const PROVIDER_ULOGIN  = 'uLogin';

    /**
     * @var ConfigProviderInterface
     */
    private $config;

    /**
     * AuthWidget constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     */
    public function __construct(ConfigProviderInterface $config)
    {
        $this->config = $config;

        parent::__construct();
    }

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

    /**
     * Returns true if current widget may be omitted during the render process
     *
     * @return bool
     */
    public function isEmptyResponseAllowed(): bool
    {
        // Always visible
        return false;
    }
}
