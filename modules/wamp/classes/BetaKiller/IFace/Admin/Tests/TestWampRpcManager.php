<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Tests;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\AbstractIFace;

class TestWampRpcManager extends AbstractIFace
{
    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @param \BetaKiller\Helper\IFaceHelper $ifaceHelper
     */
    public function __construct(
        IFaceHelper $ifaceHelper
    ) {
        $this->ifaceHelper = $ifaceHelper;
    }

    /**
     * @return string[]
     */
    public function getData(): array
    {
        $testElement = $this->ifaceHelper->getUrlElementByCodename(TestWampRpcTest::codename());
        $testUrl     = $this->ifaceHelper->makeUrl($testElement, null, false);

        return [
            'testUrl' => $testUrl,
        ];
    }
}
