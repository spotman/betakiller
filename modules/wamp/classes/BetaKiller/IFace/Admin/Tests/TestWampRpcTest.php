<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Tests;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class TestWampRpcTest extends AbstractIFace
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string[]
     */
    public function getData(ServerRequestInterface $request): array
    {
        return [
            'connectionType' => strtolower(trim(
                ServerRequestHelper::getQueryPart($request, 'connectionType', true)
            )),
            'testsQty'       => (int)ServerRequestHelper::getQueryPart($request, 'testsQty', true),
            'qtyInPack'      => (int)ServerRequestHelper::getQueryPart($request, 'qtyInPack', true),
            'delayPack'      => (int)ServerRequestHelper::getQueryPart($request, 'delayPack', true),
        ];
    }
}
