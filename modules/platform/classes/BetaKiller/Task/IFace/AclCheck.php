<?php
declare(strict_types=1);

namespace BetaKiller\Task\IFace;

use BetaKiller\Acl\UrlElementAccessResolverInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Url\ElementFilter\AggregateUrlElementFilter;
use BetaKiller\Url\ElementFilter\IFaceUrlElementFilter;
use BetaKiller\Url\UrlElementTreeInterface;

class AclCheck extends AbstractTask
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \BetaKiller\Acl\UrlElementAccessResolverInterface
     */
    private $elementAccessResolver;

    /**
     * AclCheck constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface           $tree
     * @param \BetaKiller\Model\UserInterface                   $user
     * @param \BetaKiller\Acl\UrlElementAccessResolverInterface $elementAccessResolver
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        UserInterface $user,
        UrlElementAccessResolverInterface $elementAccessResolver
    ) {
        parent::__construct();

        $this->tree                  = $tree;
        $this->user                  = $user;
        $this->elementAccessResolver = $elementAccessResolver;
    }

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
            new IFaceUrlElementFilter,
        ]);

        foreach ($this->tree->getRecursiveIteratorIterator(null, $filter) as $urlElement) {
            // TODO
            $this->elementAccessResolver->isAllowed($this->user, $urlElement);
        }
    }
}
