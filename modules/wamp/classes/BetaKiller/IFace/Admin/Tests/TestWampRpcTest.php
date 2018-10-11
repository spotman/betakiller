<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Tests;

use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Url\Container\UrlContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestWampRpcTest extends AbstractIFace
{
    /**
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     */
    public function __construct(UrlContainerInterface $urlContainer)
    {
        $this->urlContainer = $urlContainer;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string[]
     */
    public function getData(ServerRequestInterface $request): array
    {
        return [
            'connectionType' => strtolower(trim($this->findArgument('connectionType'))),
            'testQty'        => (int)$this->findArgument('testQty'),
            'countInPack'    => (int)$this->findArgument('countInPack'),
            'delayPack'      => (int)$this->findArgument('delayPack'),
        ];
    }

    /**
     * @param $name
     *
     * @return string
     */
    private function findArgument($name): string
    {
        return (string)$this->urlContainer->getQueryPart($name);
    }
}
