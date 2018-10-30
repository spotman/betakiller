<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ServerRequestInterface;

class OutdatedBrowserWidget extends AbstractPublicWidget
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $i18n     = ServerRequestHelper::getI18n($request);
        $langName = $i18n->getLang();

        return [
            'lang_name' => $langName,
        ];
    }
}
