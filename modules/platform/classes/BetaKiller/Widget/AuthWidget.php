<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Config\ConfigProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthWidget extends AbstractPublicWidget
{
    public const PROVIDER_REGULAR = 'regular';

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
    }

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     * @throws \BetaKiller\Widget\WidgetException
     */
    public function getData(ServerRequestInterface $request, array $context): array
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
