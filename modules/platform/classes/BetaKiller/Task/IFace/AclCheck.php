<?php
declare(strict_types=1);

namespace BetaKiller\Task\IFace;

use BetaKiller\Task\AbstractTask;
use BetaKiller\Url\ElementFilter\AggregateUrlElementFilter;
use BetaKiller\Url\ElementFilter\IFaceUrlElementFilter;

class AclCheck extends AbstractTask
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $filter = new AggregateUrlElementFilter([
            new IFaceUrlElementFilter
        ]);

        foreach ($this->tree->getRecursiveIteratorIterator(null, $filter) as $urlElement) {
            // TODO
            $this->aclHelper->isUrlElementAllowed($this->user, $urlElement);
        }
    }
}
