<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use Psr\Http\Message\ServerRequestInterface;

class CookiesNotificationWidget extends AbstractPublicWidget
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     * @throws \BetaKiller\Widget\WidgetException
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        return [];
    }
}
