<?php
namespace BetaKiller\Content\CustomTag;

use BetaKiller\Factory\NamespaceBasedFactory;

class CustomTagFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * CustomTagFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
            ->setClassPrefixes('Content', 'CustomTag')
            ->setClassSuffix('CustomTag')
            ->setExpectedInterface(CustomTagInterface::class);
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Content\CustomTag\CustomTagInterface
     */
    public function create(string $name): CustomTagInterface
    {
        return $this->factory->create($name);
    }
}
