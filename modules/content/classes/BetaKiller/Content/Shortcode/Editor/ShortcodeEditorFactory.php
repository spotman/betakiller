<?php
namespace BetaKiller\Content\Shortcode\Editor;

use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\Content\Shortcode\ShortcodeException;
use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

class ShortcodeEditorFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * ShortcodeEditorFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces('Content', 'Shortcode', 'Editor')
            ->setClassSuffix(ShortcodeEditorInterface::CLASS_SUFFIX)
            ->setExpectedInterface(ShortcodeEditorInterface::class);
    }

    /**
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     *
     * @return \BetaKiller\Content\Shortcode\Editor\ShortcodeEditorInterface
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromEntity(ShortcodeEntityInterface $entity): ShortcodeEditorInterface
    {
        $codename = $this->detectEditorCodename($entity);

        return $this->factory->create($codename, [
            'entity' => $entity,
        ]);
    }

    /**
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     *
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function detectEditorCodename(ShortcodeEntityInterface $entity): string
    {
        switch (true) {
            case $entity->isStatic():
                return 'Static';

            case $entity->isDynamic():
                return 'Dynamic';

            case $entity->isContentElement():
                return 'ContentElement';
                break;

            default:
                throw new ShortcodeException('Can not detect shortcode editor for [:name] shortcode', [
                    ':name' => $entity->getTagName(),
                ]);
        }
    }
}
