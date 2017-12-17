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
     * ShortcodeFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
            ->setClassPrefixes('Content', ShortcodeInterface::CLASS_NS)
            ->setClassSuffix(ShortcodeInterface::CLASS_SUFFIX)
            ->setExpectedInterface(ShortcodeInterface::class);
    }

    public function convertTagNameToCodename(string $tagName): string
    {
        // Make CamelCase naming
        return implode('', array_map('ucfirst', explode('-', $tagName)));
    }

    public function create(string $tagName, ?array $attributes = null, ?string $codename = null): ShortcodeInterface
    {
        // Make CamelCase naming
        $tagCodename = $this->convertTagNameToCodename($tagName);

        // Use tag-related class if nothing special was provided in $codename
        if (!$codename) {
            $codename = $tagCodename;
        }

        /** @var ShortcodeInterface $shortcode */
        $shortcode = $this->factory->create($codename, [
            'tagName' => $tagName,
            'codename' => $tagCodename,
        ]);

        if ($attributes) {
            $shortcode->setAttributes($attributes);
        }

        return $shortcode;
    }
}
