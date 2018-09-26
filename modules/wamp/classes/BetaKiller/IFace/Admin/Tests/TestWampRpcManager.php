<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Tests;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Url\UrlElementTreeInterface;

class TestWampRpcManager extends AbstractIFace
{
    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Helper\IFaceHelper          $ifaceHelper
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        IFaceHelper $ifaceHelper
    ) {
        $this->ifaceHelper = $ifaceHelper;
        $this->tree        = $tree;
    }

    /**
     * @return string[]
     */
    public function getData(): array
    {
        $testElement = $this->tree->getByCodename(TestWampRpcTest::codename());
        $testUrl     = $this->ifaceHelper->makeUrl($testElement, null, false);

        return [
            'testUrl' => $testUrl,
        ];
    }
}
