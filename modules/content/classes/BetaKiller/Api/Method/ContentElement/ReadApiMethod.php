<?php

namespace BetaKiller\Api\Method\ContentElement;

use BetaKiller\Content\Shortcode\ContentElementShortcodeInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

readonly class ReadApiMethod extends AbstractApiMethod
{
    /**
     * ReadApiMethod constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $facade
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function __construct(private ShortcodeFacade $facade)
    {
    }

    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string('name')
            ->identity('id');
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $name    = $arguments->getString('name');
        $modelID = $arguments->getID();

        $shortcode = $this->facade->createFromCodename($name);

        if (!$shortcode instanceof ContentElementShortcodeInterface) {
            throw new ApiMethodException('Content element [:name] must implement :must', [
                ':name' => $name,
                ':must' => ContentElementShortcodeInterface::class,
            ]);
        }

        $shortcode->setID($modelID);

        // Return data
        return $this->response(
            $shortcode->getEditorItemData()
        );
    }
}
