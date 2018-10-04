<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Tests;

use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Url\Container\UrlContainerInterface;

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
     * @return string[]
     */
    public function getData(): array
    {
        return [
            'connectionType' => strtolower(trim($this->findArgument('connectionType'))),
            'testsQty'       => (int)$this->findArgument('testsQty'),
            'qtyInPack'      => (int)$this->findArgument('qtyInPack'),
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
