<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Factory\NamespaceBasedFactory;

class ShortcodeFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ShortcodeRepository
     */
    private $repository;

    /**
     * ShortcodeFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
            ->setClassNamespaces('Content', ShortcodeInterface::CLASS_NS)
            ->setClassSuffix(ShortcodeInterface::CLASS_SUFFIX)
            ->setExpectedInterface(ShortcodeInterface::class);
    }

    public function createFromTagName(string $tagName, ?array $attributes = null): ShortcodeInterface
    {
        $urlParameter = $this->repository->findByTagName($tagName);

        return $this->createFromUrlParameter($urlParameter, $attributes);
    }

    public function createFromCodename(string $codename, ?array $attributes = null): ShortcodeInterface
    {
        $urlParameter = $this->repository->findByCodename($codename);

        return $this->createFromUrlParameter($urlParameter, $attributes);
    }

    private function getClassCodename(ShortcodeEntity $parameter): string
    {
        // Use common class for static shortcodes and shortcode-specific class for others
        return $parameter->isStatic()
            ? StaticShortcode::codename()
            : $parameter->getCodename();
    }

    public function createFromUrlParameter(ShortcodeEntity $param, ?array $attributes = null): ShortcodeInterface
    {
        $classCodename = $this->getClassCodename($param);
        $tagCodename = $param->getCodename();

        return $this->create($classCodename, $param->getTagName(), $attributes, $tagCodename);
    }

    private function create(string $classCodename, string $tagName, ?array $attributes = null, ?string $tagCodename = null): ShortcodeInterface
    {
        // Use similar codename if nothing special was provided in $tagCodename
        if (!$tagCodename) {
            $tagCodename = $classCodename;
        }

        /** @var ShortcodeInterface $shortcode */
        $shortcode = $this->factory->create($classCodename, [
            'tagName' => $tagName,
            'codename' => $tagCodename,
        ]);

        if ($attributes) {
            $shortcode->setAttributes($attributes);
        }

        return $shortcode;
    }
}
