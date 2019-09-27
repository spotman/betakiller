<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractCookiesNotificationWidget extends AbstractPublicWidget
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    abstract public function getPrivacyUrl(ServerRequestInterface $request): string;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        return [
            'privacy_url' => $this->getPrivacyUrl($request),
        ];
    }
}
