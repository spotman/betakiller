<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Repository\ShortcodeRepository;

class ShortcodeFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * @var \BetaKiller\Repository\ShortcodeRepository
     */
    private $repository;

    /**
     * ShortcodeFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     * @param \BetaKiller\Repository\ShortcodeRepository       $repo
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder, ShortcodeRepository $repo)
    {
        $this->repository = $repo;

        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces('Content', ShortcodeInterface::CLASS_NS)
            ->setClassSuffix(ShortcodeInterface::CLASS_SUFFIX)
            ->setExpectedInterface(ShortcodeInterface::class);
    }

    /**
     * @param string     $tagName
     * @param array|null $attributes
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function createFromTagName(string $tagName, ?array $attributes = null): ShortcodeInterface
    {
        $urlParameter = $this->repository->findByTagName($tagName);

        return $this->createFromEntity($urlParameter, $attributes);
    }

    /**
     * @param string     $codename
     * @param array|null $attributes
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromCodename(string $codename, ?array $attributes = null): ShortcodeInterface
    {
        $urlParameter = $this->repository->findByCodename($codename);

        return $this->createFromEntity($urlParameter, $attributes);
    }

    /**
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     *
     * @return string
     */
    private function getClassCodename(ShortcodeEntityInterface $entity): string
    {
        switch (true) {
            // Use common class for static shortcodes
            case $entity->isStatic():
                return StaticShortcode::codename();

            // Dynamic shortcodes are slightly enhanced version of StaticShortcode with dynamic templates
            case $entity->isDynamic():
                return DynamicShortcode::codename();

            // Shortcode-specific class for others
            default:
                return $entity->getCodename();
        }
    }

    /**
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     * @param array|null                                             $attributes
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromEntity(ShortcodeEntityInterface $entity, ?array $attributes = null): ShortcodeInterface
    {
        $classCodename = $this->getClassCodename($entity);

        /** @var ShortcodeInterface $shortcode */
        $shortcode = $this->factory->create($classCodename, [
            'entity' => $entity,
        ]);

        if ($attributes) {
            $shortcode->setAttributes($attributes);
        }

        return $shortcode;
    }
}
