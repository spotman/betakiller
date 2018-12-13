<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Api\Method\WampTest\DataApiMethod;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class WampRpcRunnerIFace extends AbstractIFace
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string[]
     */
    public function getData(ServerRequestInterface $request): array
    {
        $case = ServerRequestHelper::getQueryPart($request, 'case', true);

        return [
            'connection_type' => strtolower(trim(
                ServerRequestHelper::getQueryPart($request, 'connectionType', true)
            )),
            'case'            => $case,
            'case_value'      => json_encode(DataApiMethod::makeTestResponse($case)),
            'tests_qty'       => (int)ServerRequestHelper::getQueryPart($request, 'testsQty', true),
            'qty_in_pack'     => (int)ServerRequestHelper::getQueryPart($request, 'qtyInPack', true),
            'delay_pack'      => (int)ServerRequestHelper::getQueryPart($request, 'delayPack', true),
        ];
    }
}
