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
            'connectionType' => strtolower(trim($this->findArgument($request, 'connectionType'))),
            'testsQty'       => (int)$this->findArgument($request, 'testsQty'),
            'qtyInPack'      => (int)$this->findArgument($request, 'qtyInPack'),
            'delayPack'      => (int)$this->findArgument($request, 'delayPack'),
        ];
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string                                   $name
     *
     * @return string
     */
    private function findArgument(ServerRequestInterface $request, string $name): string
    {
        return (string)ServerRequestHelper::getUrlContainer($request)->getQueryPart($name);
    }
}
